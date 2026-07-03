<?php

declare(strict_types=1);

namespace App\Platform\Automation\Infrastructure\Listeners;

use App\Platform\Automation\Application\Services\AutomationEngine;
use App\Platform\Automation\Domain\Registry\AutomationEventRegistry;
use Illuminate\Contracts\Events\Dispatcher;

class AutomationEventListener
{
    public function __construct(
        protected AutomationEngine $engine
    ) {}

    /**
     * Handle incoming domain event and evaluate automation rules.
     */
    public function handleEvent(object $event): void
    {
        try {
            $eventClass = get_class($event);
            
            // Convert event properties to array payload
            $eventPayload = [];
            $reflection = new \ReflectionClass($event);
            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                $eventPayload[$property->getName()] = $property->getValue($event);
            }

            // Fallback for aggregate information if not explicitly in array
            if (isset($event->aggregateId)) {
                $eventPayload['entity_id'] = $event->aggregateId;
                
                // Add entity type if matched from event FQCN
                if (str_contains($eventClass, 'Booking')) {
                    $eventPayload['entity_type'] = \App\Modules\Bookings\Domain\Entities\Booking::class;
                    // Add nested booking payload for context dot notation
                    $booking = \App\Modules\Bookings\Domain\Entities\Booking::find($event->aggregateId);
                    if ($booking) {
                        $eventPayload['booking'] = $booking->toArray();
                    }
                } elseif (str_contains($eventClass, 'Invoice')) {
                    $eventPayload['entity_type'] = \App\Modules\Finance\Domain\Entities\Invoice::class;
                    $invoice = \App\Modules\Finance\Domain\Entities\Invoice::find($event->aggregateId);
                    if ($invoice) {
                        $eventPayload['invoice'] = $invoice->toArray();
                    }
                } elseif (str_contains($eventClass, 'Lead')) {
                    $eventPayload['entity_type'] = \App\Modules\CRM\Domain\Entities\Lead::class;
                    $lead = \App\Modules\CRM\Domain\Entities\Lead::find($event->aggregateId);
                    if ($lead) {
                        $eventPayload['lead'] = $lead->toArray();
                    }
                }
            }

            $this->engine->run($eventClass, $eventPayload);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Automation event listener caught exception: " . $e->getMessage(), [
                'exception' => $e,
                'event' => $event,
            ]);
        }
    }

    /**
     * Register listeners.
     */
    public function subscribe(Dispatcher $events): void
    {
        foreach (AutomationEventRegistry::getEvents() as $eventClass) {
            $events->listen($eventClass, [self::class, 'handleEvent']);
        }
    }
}
