<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Application\Jobs;

use App\Platform\Notifications\Domain\Entities\NotificationDispatch;
use App\Platform\Notifications\Domain\Entities\NotificationAttempt;
use App\Platform\Notifications\Domain\Entities\NotificationChannel;
use App\Platform\Notifications\Domain\Enums\NotificationStatus;
use App\Platform\Notifications\Infrastructure\Registry\ChannelRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $dispatchId;

    public function __construct(string $dispatchId)
    {
        $this->dispatchId = $dispatchId;
    }

    public function handle(ChannelRegistry $registry): void
    {
        $dispatch = NotificationDispatch::find($this->dispatchId);
        if (!$dispatch) {
            return;
        }

        // Only run if queued or retrying
        if ($dispatch->status !== NotificationStatus::QUEUED && $dispatch->status !== NotificationStatus::FAILED) {
            // Wait, we can allow processing retries
        }

        $dispatch->update(['status' => NotificationStatus::PROCESSING]);

        // Resolve driver from registry
        $driver = $registry->get($dispatch->channel);

        // Fetch channel config
        $channelConfig = NotificationChannel::where('key', $dispatch->channel)->first();
        $maxAttempts = $channelConfig ? $channelConfig->retry_attempts : 3;
        $currentAttemptNumber = $dispatch->attempts()->count() + 1;

        // Compile subject and body
        $version = $dispatch->templateVersion;
        $context = $dispatch->context_snapshot ?? [];

        $compiler = app(\App\Platform\Notifications\Application\TemplateCompiler\TemplateCompiler::class);
        
        $subject = null;
        if ($version && $version->subject) {
            $subject = $compiler->compile($version->subject, $context);
        }

        // Content holds channel mappings: content => [channel => [title, body, etc.]]
        $body = '';
        $options = ['dispatch_id' => $dispatch->id];
        
        if ($version && is_array($version->content) && isset($version->content[$dispatch->channel])) {
            $channelContent = $version->content[$dispatch->channel];
            $bodyTemplate = $channelContent['body'] ?? '';
            $body = $compiler->compile($bodyTemplate, $context);

            if (isset($channelContent['title'])) {
                $options['type'] = $channelContent['title']; // in-app notification types
            }
            if (isset($channelContent['button_url'])) {
                $options['link_url'] = $compiler->compile($channelContent['button_url'], $context);
            }
        } else {
            // Fallback body
            $body = 'Notification payload';
        }

        // Fetch DAM attachments linked to this dispatch
        $options['attachments'] = [];
        $attachments = $dispatch->attachments;
        foreach ($attachments as $attachment) {
            $file = $attachment->asset?->currentVersion?->file;
            if ($file) {
                // If local disk, build absolute path
                $options['attachments'][] = storage_path('app/public/' . $file->path);
            }
        }

        // Run send
        $result = $driver->send($dispatch->recipient_contact, $body, $subject, $options);

        // Log attempt
        NotificationAttempt::create([
            'id' => (string) Str::uuid(),
            'dispatch_id' => $dispatch->id,
            'attempt_number' => $currentAttemptNumber,
            'provider_name' => $dispatch->channel,
            'provider_reference' => $result['provider_reference'] ?? null,
            'provider_response' => $result['provider_response'] ?? null,
            'provider_status_code' => $result['provider_status_code'] ?? null,
            'status' => $result['success'] ? 'sent' : 'failed',
            'error_message' => $result['error_message'] ?? null,
            'payload' => $options,
            'created_at' => now(),
        ]);

        if ($result['success']) {
            $dispatch->update([
                'status' => NotificationStatus::SENT,
            ]);

            // Fire domain event
            event('NotificationSent', $dispatch->id);
        } else {
            if ($currentAttemptNumber < $maxAttempts) {
                $dispatch->update([
                    'status' => NotificationStatus::FAILED, // Will be retried or marked as failed
                ]);
                
                // Release back to queue or trigger retry delay
                $this->release($channelConfig ? $channelConfig->timeout_seconds : 30);
            } else {
                $dispatch->update([
                    'status' => NotificationStatus::FAILED,
                ]);

                // Fire domain event
                event('NotificationFailed', $dispatch->id);
            }
        }
    }
}
