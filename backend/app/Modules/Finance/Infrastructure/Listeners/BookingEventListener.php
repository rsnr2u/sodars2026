<?php

declare(strict_types=1);

namespace App\Modules\Finance\Infrastructure\Listeners;

use App\Modules\Bookings\Domain\Events\BookingCreated;
use App\Modules\Bookings\Domain\Events\PaymentAudited;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\PaymentStatus;
use App\Modules\Finance\Application\Services\FinanceService;
use App\Modules\Finance\Domain\Entities\Invoice;

class BookingEventListener
{
    public function __construct(protected FinanceService $financeService) {}

    public function handleBookingCreated(BookingCreated $event): void
    {
        $bookingId = $event->aggregateId;
        $booking = Booking::with('items.face.inventory.provider', 'customer', 'branch')->find($bookingId);

        if ($booking) {
            // Auto generate draft Proforma Invoice when booking checkout completes
            $this->financeService->createProforma($booking);
        }
    }

    public function handlePaymentAudited(PaymentAudited $event): void
    {
        $bookingId = $event->aggregateId;
        $booking = Booking::with('items.face.inventory.provider', 'customer', 'branch')->find($bookingId);

        if ($booking && $event->data['status'] === PaymentStatus::Verified->value) {
            // Auto generate Tax Invoice when offline payment is verified
            $invoice = $this->financeService->createTaxInvoice($booking);

            // Auto transition tax invoice to issued state
            $this->financeService->issue($invoice->id);

            // Auto generate Provider Settlement statement splits
            $this->financeService->generateSettlement($bookingId, $invoice->id);
        }
    }

    public function subscribe(mixed $events): void
    {
        $events->listen(
            BookingCreated::class,
            [self::class, 'handleBookingCreated']
        );

        $events->listen(
            PaymentAudited::class,
            [self::class, 'handlePaymentAudited']
        );
    }
}
