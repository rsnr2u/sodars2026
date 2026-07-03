<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Services;

use App\Modules\Bookings\Application\Actions\CreateBookingAction;
use App\Modules\Bookings\Application\Actions\RecordPaymentAction;
use App\Modules\Bookings\Application\Actions\AuditPaymentAction;
use App\Modules\Bookings\Application\Actions\ApproveBookingAction;
use App\Modules\Bookings\Application\Actions\RejectBookingAction;
use App\Modules\Bookings\Application\Actions\CancelBookingAction;
use App\Modules\Bookings\Application\Queries\ListBookingsQuery;
use App\Modules\Bookings\Application\Queries\GetBookingDetailsQuery;
use App\Modules\Bookings\Application\Queries\BookingDashboardQuery;
use App\Modules\Bookings\Application\DTOs\CreateBookingData;
use App\Modules\Bookings\Application\DTOs\RecordPaymentData;
use App\Modules\Bookings\Application\DTOs\BookingFilterData;
use App\Modules\Bookings\Application\DTOs\BookingDashboardDTO;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Entities\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingService
{
    public function __construct(
        protected CreateBookingAction $createAction,
        protected RecordPaymentAction $recordPaymentAction,
        protected AuditPaymentAction $auditPaymentAction,
        protected ApproveBookingAction $approveAction,
        protected RejectBookingAction $rejectAction,
        protected CancelBookingAction $cancelAction,
        protected ListBookingsQuery $listQuery,
        protected GetBookingDetailsQuery $detailsQuery,
        protected BookingDashboardQuery $dashboardQuery
    ) {}

    public function create(CreateBookingData $dto): Booking
    {
        return $this->createAction->execute($dto);
    }

    public function recordPayment(string $bookingId, RecordPaymentData $dto): Payment
    {
        return $this->recordPaymentAction->execute($bookingId, $dto);
    }

    public function auditPayment(string $bookingId, string $paymentId, string $status): Payment
    {
        return $this->auditPaymentAction->execute($bookingId, $paymentId, $status);
    }

    public function approve(string $bookingId, ?string $comment = null): Booking
    {
        return $this->approveAction->execute($bookingId, $comment);
    }

    public function reject(string $bookingId, ?string $comment = null): Booking
    {
        return $this->rejectAction->execute($bookingId, $comment);
    }

    public function cancel(string $bookingId, ?string $comment = null): Booking
    {
        return $this->cancelAction->execute($bookingId, $comment);
    }

    /**
     * @return LengthAwarePaginator<Booking>
     */
    public function list(BookingFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery->execute($filters, $perPage);
    }

    public function getDetails(string $id): Booking
    {
        return $this->detailsQuery->execute($id);
    }

    public function getDashboard(?string $customerId = null): BookingDashboardDTO
    {
        return $this->dashboardQuery->execute($customerId);
    }
}
