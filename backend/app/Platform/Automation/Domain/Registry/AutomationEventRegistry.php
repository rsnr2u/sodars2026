<?php

declare(strict_types=1);

namespace App\Platform\Automation\Domain\Registry;

class AutomationEventRegistry
{
    /**
     * List of fully qualified domain event classes that can trigger automation rules.
     *
     * @var array<int, string>
     */
    private static array $events = [
        \App\Modules\Bookings\Domain\Events\BookingCreated::class,
        \App\Modules\Bookings\Domain\Events\BookingStatusChanged::class,
        \App\Modules\Finance\Domain\Events\InvoiceCreated::class,
        \App\Modules\CRM\Domain\Events\LeadCreated::class,
    ];

    /**
     * Get all registered events.
     *
     * @return array<int, string>
     */
    public static function getEvents(): array
    {
        return self::$events;
    }

    /**
     * Register a new event in the registry dynamically.
     */
    public static function register(string $eventClass): void
    {
        if (!in_array($eventClass, self::$events, true)) {
            self::$events[] = $eventClass;
        }
    }
}
