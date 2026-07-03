<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Entities\Payment;
use App\Modules\Bookings\Domain\Entities\BookingActivity;
use App\Modules\Bookings\Domain\Enums\PaymentStatus;
use App\Modules\Bookings\Domain\Events\PaymentAudited;
use App\Modules\Bookings\Domain\Repositories\BookingReadRepositoryInterface;
use App\Modules\Bookings\Domain\Repositories\BookingPaymentRepositoryInterface;
use App\Modules\Bookings\Domain\Services\BookingLifecycleService;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class AuditPaymentAction
{
    public function __construct(
        protected BookingReadRepositoryInterface $bookingReadRepo,
        protected BookingPaymentRepositoryInterface $paymentRepo,
        protected BookingLifecycleService $lifecycleService,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $bookingId, string $paymentId, string $status): Payment
    {
        return DB::transaction(function () use ($bookingId, $paymentId, $status) {
            $booking = $this->bookingReadRepo->findOrFail($bookingId);
            $payment = $this->paymentRepo->findOrFail($paymentId);

            $payment = $this->paymentRepo->update($paymentId, [
                'status' => $status,
            ]);

            // If payment is verified, transition booking to provider_review status
            if ($status === PaymentStatus::Verified->value) {
                if ($booking->status === BookingStatus::BranchReview) {
                    $this->lifecycleService->transition($booking, BookingStatus::ProviderReview->value, 'Payment verified. Submitted for provider availability review.');
                }
            } else {
                // Payment failed/rejected, revert to rejected state
                $this->lifecycleService->transition($booking, BookingStatus::Rejected->value, 'Offline payment verification failed.');
            }

            $eventData = [
                'payment_id' => $paymentId,
                'booking_id' => $bookingId,
                'status' => $status,
            ];

            // Outbox
            $this->outboxService->record(
                aggregateType: 'Booking',
                aggregateId: $bookingId,
                eventName: 'booking.payment_audited.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // Event
            Event::dispatch(new PaymentAudited(
                aggregateId: $bookingId,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) Str::uuid()
            ));

            // Activity
            BookingActivity::create([
                'id' => (string) Str::uuid(),
                'booking_id' => $bookingId,
                'performed_by' => auth()->id(),
                'event_name' => 'booking.payment_audited.v1',
                'action' => 'PaymentAudited',
                'old_values' => null,
                'new_values' => $payment->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

            return $payment;
        });
    }
}
