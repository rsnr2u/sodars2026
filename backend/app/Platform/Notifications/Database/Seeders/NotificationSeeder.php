<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Database\Seeders;

use App\Platform\Notifications\Domain\Entities\NotificationChannel;
use App\Platform\Notifications\Domain\Entities\NotificationTemplate;
use App\Platform\Notifications\Domain\Entities\NotificationTemplateVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Configurable Channels
        $channels = [
            [
                'key' => 'email',
                'driver' => 'smtp',
                'priority' => 1,
                'retry_attempts' => 3,
                'timeout_seconds' => 30,
            ],
            [
                'key' => 'sms',
                'driver' => 'sms_mock',
                'priority' => 2,
                'retry_attempts' => 2,
                'timeout_seconds' => 15,
            ],
            [
                'key' => 'whatsapp',
                'driver' => 'whatsapp_mock',
                'priority' => 3,
                'retry_attempts' => 2,
                'timeout_seconds' => 20,
            ],
            [
                'key' => 'push',
                'driver' => 'push_mock',
                'priority' => 4,
                'retry_attempts' => 2,
                'timeout_seconds' => 10,
            ],
            [
                'key' => 'in_app',
                'driver' => 'local_db',
                'priority' => 0,
                'retry_attempts' => 1,
                'timeout_seconds' => 5,
            ],
        ];

        foreach ($channels as $c) {
            NotificationChannel::updateOrCreate(
                ['key' => $c['key']],
                [
                    'id' => (string) Str::uuid(),
                    'driver' => $c['driver'],
                    'is_enabled' => true,
                    'priority' => $c['priority'],
                    'retry_attempts' => $c['retry_attempts'],
                    'timeout_seconds' => $c['timeout_seconds'],
                    'configuration' => [],
                ]
            );
        }

        // 2. Seed Templates and Active Versions
        $this->seedTemplate(
            'booking.created',
            'Booking Created Notification',
            'transactional',
            'Booking Confirmation: {{booking.booking_code}}',
            [
                'email' => [
                    'body' => 'Hello {{customer.name}}, your booking {{booking.booking_code}} has been successfully created. Total: {{booking.total_amount}}.'
                ],
                'in_app' => [
                    'title' => 'success',
                    'body' => 'Booking {{booking.booking_code}} has been created.'
                ]
            ]
        );

        $this->seedTemplate(
            'booking.status_changed',
            'Booking Status Changed Notification',
            'transactional',
            'Booking {{booking.booking_code}} Status Update',
            [
                'email' => [
                    'body' => 'Dear {{customer.name}}, the status of your booking {{booking.booking_code}} is now {{booking.status}}.'
                ],
                'in_app' => [
                    'title' => 'info',
                    'body' => 'Booking {{booking.booking_code}} transitioned to {{booking.status}}.'
                ]
            ]
        );

        $this->seedTemplate(
            'invoice.created',
            'Invoice Generated Notification',
            'finance',
            'Tax Invoice Generated: {{invoice.invoice_number}}',
            [
                'email' => [
                    'body' => 'A new tax invoice {{invoice.invoice_number}} has been generated for booking code {{booking.booking_code}} with total amount {{invoice.total}}.'
                ],
                'in_app' => [
                    'title' => 'success',
                    'body' => 'Invoice {{invoice.invoice_number}} generated.'
                ]
            ]
        );
    }

    private function seedTemplate(string $key, string $name, string $category, string $subject, array $content): void
    {
        $template = NotificationTemplate::updateOrCreate(
            ['key' => $key],
            [
                'id' => (string) Str::uuid(),
                'name' => $name,
                'category' => $category,
                'active_version_number' => 1,
            ]
        );

        // Find or create version 1
        $version = NotificationTemplateVersion::where('template_id', $template->id)
            ->where('version_number', 1)
            ->first();

        if (!$version) {
            NotificationTemplateVersion::create([
                'id' => (string) Str::uuid(),
                'template_id' => $template->id,
                'version_number' => 1,
                'subject' => $subject,
                'content' => $content,
                'is_active' => true,
            ]);
        } else {
            $version->update([
                'subject' => $subject,
                'content' => $content,
                'is_active' => true,
            ]);
        }
    }
}
