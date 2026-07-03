<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Bookings\Application\DTOs\CreateBookingData;
use App\Modules\Bookings\Application\DTOs\BookingFilterData;
use App\Modules\Bookings\Application\Services\BookingService;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Presentation\Requests\CreateBookingRequest;
use App\Modules\Bookings\Presentation\Requests\AuditBookingRequest;
use App\Modules\Bookings\Presentation\Resources\BookingResource;
use App\Modules\Bookings\Presentation\Resources\BookingDetailResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookingController extends BaseApiController
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Booking::class);

        $filters = BookingFilterData::fromRequest($request);
        $bookings = $this->bookingService->list($filters, (int) $request->query('per_page', 15));

        return $this->successResponse(
            BookingResource::collection($bookings)->response()->getData(true),
            'Booking records catalog retrieved successfully.'
        );
    }

    public function store(CreateBookingRequest $request): JsonResponse
    {
        Gate::authorize('create', Booking::class);

        $dto = CreateBookingData::fromRequest($request);
        $booking = $this->bookingService->create($dto);

        return $this->successResponse(
            new BookingResource($booking),
            'Booking transaction checkout generated successfully.',
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $booking = $this->bookingService->getDetails($id);
        Gate::authorize('view', $booking);

        $booking->load(['customer', 'branch', 'items.face.inventory', 'payments', 'statusHistory', 'notes', 'activities']);

        return $this->successResponse(
            new BookingDetailResource($booking),
            'Booking detail snapshot retrieved successfully.'
        );
    }

    public function audit(string $id, AuditBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->getDetails($id);
        Gate::authorize('auditBooking', $booking);

        $status = $request->input('status');
        $comment = $request->input('comment');

        $updated = match ($status) {
            'approved' => $this->bookingService->approve($id, $comment),
            'rejected' => $this->bookingService->reject($id, $comment),
            'cancelled' => $this->bookingService->cancel($id, $comment),
        };

        return $this->successResponse(
            new BookingResource($updated),
            'Booking workflow review completed successfully.'
        );
    }

    public function dashboard(Request $request): JsonResponse
    {
        $customerId = $request->query('customer_id');
        $dashboard = $this->bookingService->getDashboard($customerId);

        return $this->successResponse(
            $dashboard->toArray(),
            'Booking transaction summary dashboard calculated successfully.'
        );
    }
}
