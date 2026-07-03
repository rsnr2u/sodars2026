<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Application\Services;

use App\Models\User;
use App\Platform\Notifications\Application\Jobs\SendNotificationJob;
use App\Platform\Notifications\Domain\Entities\NotificationDispatch;
use App\Platform\Notifications\Domain\Entities\NotificationTemplate;
use App\Platform\Notifications\Domain\Entities\NotificationPreference;
use App\Platform\Notifications\Domain\Entities\NotificationAttachment;
use App\Platform\Notifications\Domain\Enums\NotificationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Dispatch notification to all preferred channels for a template.
     */
    public function send(
        string $userId,
        string $templateKey,
        array $context = [],
        array $attachmentAssetIds = []
    ): void {
        $user = User::find($userId);
        if (!$user) {
            \Illuminate\Support\Facades\Log::warning("User not found for notification. User ID: {$userId}");
            return;
        }

        $template = NotificationTemplate::where('key', $templateKey)->first();
        if (!$template) {
            \Illuminate\Support\Facades\Log::warning("Notification template not found for key: {$templateKey}");
            return;
        }

        $version = $template->versions()->where('is_active', true)->first();
        if (!$version) {
            \Illuminate\Support\Facades\Log::warning("Notification active template version not found for key: {$templateKey}");
            return;
        }

        // 1. Determine which channels this template has content configured for
        $configuredChannels = is_array($version->content) ? array_keys($version->content) : [];

        foreach ($configuredChannels as $channel) {
            // 2. Check user's specific notification preference
            $preference = NotificationPreference::where('user_id', $user->id)
                ->where('category', $template->category->value)
                ->where('channel', $channel)
                ->first();

            // Skip channel if user explicitly disabled it
            if ($preference && !$preference->is_enabled) {
                continue;
            }

            // 3. Resolve destination contact point
            $contact = '';
            if ($channel === 'email') {
                $contact = $user->email;
            } elseif ($channel === 'sms' || $channel === 'whatsapp') {
                $contact = $user->phone ?? '+919999999999'; // fallback mock
            } elseif ($channel === 'push') {
                $contact = $user->device_token ?? 'push-token-mock';
            } elseif ($channel === 'in_app') {
                $contact = $user->id; // user id is the target recipient key in DB In-App table
            }

            DB::transaction(function () use ($user, $template, $version, $channel, $contact, $context, $attachmentAssetIds) {
                // 4. Create NotificationDispatch record
                $dispatch = NotificationDispatch::create([
                    'id' => (string) Str::uuid(),
                    'template_id' => $template->id,
                    'template_version_id' => $version->id,
                    'recipient_id' => $user->id,
                    'recipient_contact' => $contact,
                    'channel' => $channel,
                    'status' => NotificationStatus::QUEUED,
                    'context_snapshot' => $context,
                ]);

                // 5. Link DAM Attachments
                foreach ($attachmentAssetIds as $assetId) {
                    NotificationAttachment::create([
                        'id' => (string) Str::uuid(),
                        'dispatch_id' => $dispatch->id,
                        'asset_id' => $assetId,
                    ]);
                }

                // 6. Push to Queue
                SendNotificationJob::dispatch($dispatch->id);
            });
        }
    }

    /**
     * Dispatch notification to a bulk list of recipients.
     */
    public function sendBulk(
        iterable $userIds,
        string $templateKey,
        array $context = [],
        array $attachmentAssetIds = []
    ): void {
        foreach ($userIds as $userId) {
            $this->send($userId, $templateKey, $context, $attachmentAssetIds);
        }
    }
}
