<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Infrastructure\Drivers;

use App\Platform\Notifications\Domain\Contracts\ChannelDriver;
use Illuminate\Support\Facades\Mail;

class EmailDriver implements ChannelDriver
{
    public function send(string $recipient, string $body, ?string $subject = null, array $options = []): array
    {
        try {
            $subj = $subject ?? 'Notification Update';
            
            // Dispatch via Laravel Mail facade
            Mail::html($body, function ($message) use ($recipient, $subj, $options) {
                $message->to($recipient)
                        ->subject($subj);

                // Add physical attachments if supplied from DAM assets
                foreach ($options['attachments'] ?? [] as $attachmentPath) {
                    if (file_exists($attachmentPath)) {
                        $message->attach($attachmentPath);
                    }
                }
            });

            return [
                'success' => true,
                'provider_reference' => 'smtp-' . uniqid(),
                'provider_response' => 'Email sent successfully via Laravel Mail.',
                'provider_status_code' => 250,
                'error_message' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'provider_reference' => null,
                'provider_response' => null,
                'provider_status_code' => 500,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    public function getCapabilities(): array
    {
        return [
            'supports_subject' => true,
            'supports_attachment' => true,
            'supports_html' => true,
            'supports_template' => true,
            'supports_buttons' => true,
        ];
    }
}
