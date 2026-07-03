<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Infrastructure\Listeners;

use App\Models\User;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Events\BookingCreated;
use App\Modules\Bookings\Domain\Events\BookingStatusChanged;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Events\InvoiceCreated;
use App\Platform\Notifications\Application\Services\NotificationService;
use Illuminate\Events\Dispatcher;

class NotificationEventListener
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle Booking Created Event.
     */
    public function handleBookingCreated(BookingCreated $event): void
    {
        try {
            $booking = Booking::find($event->aggregateId);
            if (!$booking) {
                return;
            }

            // Context compiling
            $context = [
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'status' => $booking->status->value,
                    'total_amount' => $booking->total_amount,
                ],
                'customer' => [
                    'name' => $booking->customer?->name ?? 'Valued Customer',
                    'email' => $booking->customer?->email ?? '',
                ]
            ];

            // Find recipient user
            $user = null;
            if ($booking->customer?->email) {
                $user = User::where('email', $booking->customer->email)->first();
            }
            if (!$user && $event->userId) {
                $user = User::find($event->userId);
            }
            $user = $user ?? User::first(); // Fallback to first user in system

            if ($user) {
                $this->notificationService->send($user->id, 'booking.created', $context);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Notification listener failed for BookingCreated: " . $e->getMessage(), [
                'exception' => $e,
                'event' => $event,
            ]);
        }
    }

    /**
     * Handle Booking Status Changed Event.
     */
    public function handleBookingStatusChanged(BookingStatusChanged $event): void
    {
        try {
            $booking = Booking::find($event->aggregateId);
            if (!$booking) {
                return;
            }

            $context = [
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'status' => $booking->status->value,
                    'total_amount' => $booking->total_amount,
                ],
                'customer' => [
                    'name' => $booking->customer?->name ?? 'Valued Customer',
                    'email' => $booking->customer?->email ?? '',
                ]
            ];

            $user = null;
            if ($booking->customer?->email) {
                $user = User::where('email', $booking->customer->email)->first();
            }
            if (!$user && $event->userId) {
                $user = User::find($event->userId);
            }
            $user = $user ?? User::first();

            if ($user) {
                $this->notificationService->send($user->id, 'booking.status_changed', $context);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Notification listener failed for BookingStatusChanged: " . $e->getMessage(), [
                'exception' => $e,
                'event' => $event,
            ]);
        }
    }

    /**
     * Handle Invoice Created Event.
     */
    public function handleInvoiceCreated(InvoiceCreated $event): void
    {
        try {
            $invoice = Invoice::find($event->aggregateId);
            if (!$invoice) {
                return;
            }

            $context = [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'total' => $invoice->total,
                ],
                'booking' => [
                    'booking_code' => $invoice->booking?->booking_code ?? '',
                ]
            ];

            $user = null;
            if ($invoice->booking?->customer?->email) {
                $user = User::where('email', $invoice->booking->customer->email)->first();
            }
            if (!$user && $event->userId) {
                $user = User::find($event->userId);
            }
            $user = $user ?? User::first();

            if ($user) {
                $this->notificationService->send($user->id, 'invoice.created', $context);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Notification listener failed for InvoiceCreated: " . $e->getMessage(), [
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
        $events->listen(
            BookingCreated::class,
            [self::class, 'handleBookingCreated']
        );

        $events->listen(
            BookingStatusChanged::class,
            [self::class, 'handleBookingStatusChanged']
        );

        $events->listen(
            InvoiceCreated::class,
            [self::class, 'handleInvoiceCreated']
        );
    }
}
