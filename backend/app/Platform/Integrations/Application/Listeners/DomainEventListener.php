<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Application\Listeners;

use App\Modules\Bookings\Domain\Events\BookingCreated;
use App\Modules\Bookings\Domain\Events\BookingStatusChanged;
use App\Modules\Finance\Domain\Events\InvoiceCreated;
use App\Modules\Inventory\Domain\Events\InventoryCreated;
use App\Platform\Integrations\Domain\Webhooks\WebhookSubscription;
use App\Platform\Integrations\Application\Jobs\DeliverWebhookJob;

class DomainEventListener
{
    /**
     * Map system domain event FQCN to webhook event key.
     */
    protected array $eventMap = [
        BookingCreated::class => 'booking.created',
        BookingStatusChanged::class => 'booking.status_changed',
        InvoiceCreated::class => 'invoice.created',
        InventoryCreated::class => 'inventory.created',
    ];

    /**
     * Listen and handle incoming domain events.
     */
    public function handle(mixed $event): void
    {
        $eventClass = get_class($event);
        if (!isset($this->eventMap[$eventClass])) {
            return;
        }

        $eventType = $this->eventMap[$eventClass];
        
        $payloadData = [];
        if (isset($event->data) && is_array($event->data)) {
            $payloadData = $event->data;
        } elseif (method_exists($event, 'toArray')) {
            $payloadData = $event->toArray();
        }

        $subscriptions = WebhookSubscription::where('is_active', true)->get();

        foreach ($subscriptions as $sub) {
            $types = (array) ($sub->event_types ?? []);
            if (in_array($eventType, $types, true)) {
                DeliverWebhookJob::dispatch($sub->id, $eventType, $payloadData);
            }
        }
    }

    /**
     * Register listeners in the Event dispatcher.
     */
    public function subscribe(mixed $events): void
    {
        foreach (array_keys($this->eventMap) as $eventClass) {
            $events->listen($eventClass, [self::class, 'handle']);
        }
    }
}
