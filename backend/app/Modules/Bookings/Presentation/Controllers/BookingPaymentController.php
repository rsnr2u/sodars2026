<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Bookings\Application\DTOs\RecordPaymentData;
use App\Modules\Bookings\Application\Services\BookingService;
use App\Modules\Bookings\Presentation\Requests\RecordPaymentRequest;
use App\Modules\Bookings\Presentation\Requests\AuditPaymentRequest;
use App\Modules\Bookings\Presentation\Resources\BookingPaymentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BookingPaymentController extends BaseApiController
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    public function store(string $bookingId, RecordPaymentRequest $request): JsonResponse
    {
        $booking = $this->bookingService->getDetails($bookingId);
        Gate::authorize('recordPayment', $booking);

        $dto = RecordPaymentData::fromRequest($request);
        $payment = $this->bookingService->recordPayment($bookingId, $dto);

        return $this->successResponse(
            new BookingPaymentResource($payment),
            'Offline transaction recorded in payment ledger.',
            201
        );
    }

    public function audit(string $bookingId, string $paymentId, AuditPaymentRequest $request): JsonResponse
    {
        Gate::authorize('auditPayment', Booking::class);

        $payment = $this->bookingService->auditPayment(
            $bookingId,
            $paymentId,
            $request->input('status')
        );

        return $this->successResponse(
            new BookingPaymentResource($payment),
            'Offline transaction audit completed successfully.'
        );
    }
}
