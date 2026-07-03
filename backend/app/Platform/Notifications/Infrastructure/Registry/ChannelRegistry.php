<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Infrastructure\Registry;

use App\Platform\Notifications\Domain\Contracts\ChannelDriver;
use App\Platform\Notifications\Infrastructure\Drivers\EmailDriver;
use App\Platform\Notifications\Infrastructure\Drivers\SmsDriver;
use App\Platform\Notifications\Infrastructure\Drivers\WhatsAppDriver;
use App\Platform\Notifications\Infrastructure\Drivers\PushDriver;
use App\Platform\Notifications\Infrastructure\Drivers\InAppDriver;
use RuntimeException;

class ChannelRegistry
{
    protected array $drivers = [];

    public function __construct()
    {
        // Register default drivers
        $this->register('email', new EmailDriver());
        $this->register('sms', new SmsDriver());
        $this->register('whatsapp', new WhatsAppDriver());
        $this->register('push', new PushDriver());
        $this->register('in_app', new InAppDriver());
    }

    /**
     * Register a driver for a channel key.
     */
    public function register(string $channelKey, ChannelDriver $driver): void
    {
        $this->drivers[$channelKey] = $driver;
    }

    /**
     * Resolve the channel driver instance.
     */
    public function get(string $channelKey): ChannelDriver
    {
        if (!isset($this->drivers[$channelKey])) {
            throw new RuntimeException("Notification channel driver [{$channelKey}] is not registered.");
        }

        return $this->drivers[$channelKey];
    }
}
