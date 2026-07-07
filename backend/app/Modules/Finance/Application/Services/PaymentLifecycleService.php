<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Services;

use App\Core\Services\OutboxService;
use App\Modules\Finance\Domain\Entities\Payment;
use App\Modules\Finance\Domain\Events\PaymentReceived;
use App\Modules\Finance\Domain\Events\PaymentFailed;
use Illuminate\Support\Facades\Event;

class PaymentLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    /**
     * Record payment received event.
     */
    public function recordReceived(Payment $payment): void
    {
        $eventData = [
            'payment_id' => $payment->id,
            'reference_number' => $payment->reference_number,
            'amount_cents' => $payment->amount_cents,
        ];

        $metadata = [
            'payment_method' => $payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : (string) $payment->payment_method,
            'status' => $payment->status instanceof \BackedEnum ? $payment->status->value : (string) $payment->status,
        ];

        $this->outboxService->record(
            aggregateType: 'Payment',
            aggregateId: $payment->id,
            eventName: 'finance.payment.received.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new PaymentReceived(
            entityClass: Payment::class,
            aggregateId: $payment->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }

    /**
     * Record payment failure event.
     */
    public function recordFailure(Payment $payment): void
    {
        $eventData = [
            'payment_id' => $payment->id,
            'reference_number' => $payment->reference_number,
            'amount_cents' => $payment->amount_cents,
        ];

        $metadata = [
            'payment_method' => $payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : (string) $payment->payment_method,
            'status' => $payment->status instanceof \BackedEnum ? $payment->status->value : (string) $payment->status,
        ];

        $this->outboxService->record(
            aggregateType: 'Payment',
            aggregateId: $payment->id,
            eventName: 'finance.payment.failed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new PaymentFailed(
            entityClass: Payment::class,
            aggregateId: $payment->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }
}
