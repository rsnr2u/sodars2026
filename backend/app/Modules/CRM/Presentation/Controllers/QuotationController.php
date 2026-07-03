<?php

declare(strict_types=1);

namespace App\Modules\CRM\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\CRM\Application\Actions\CreateQuotationAction;
use App\Modules\CRM\Application\Actions\ConvertQuotationAction;
use App\Modules\CRM\Application\Queries\GetQuotationDetailsQuery;
use App\Modules\CRM\Presentation\Requests\CreateQuotationRequest;
use App\Modules\CRM\Presentation\Resources\QuotationResource;
use App\Modules\Bookings\Presentation\Resources\BookingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationController extends BaseApiController
{
    /**
     * Create new versioned quotation proposal.
     */
    public function store(CreateQuotationRequest $request, CreateQuotationAction $action): JsonResponse
    {
        $quote = $action->execute($request->validated());
        $quote->load(['activeVersion.items']);

        return $this->successResponse(
            new QuotationResource($quote),
            'Quotation proposal created successfully.',
            201
        );
    }

    /**
     * Show quotation detail parameters.
     */
    public function show(string $id, GetQuotationDetailsQuery $query): JsonResponse
    {
        $quote = $query->execute($id);

        return $this->successResponse(
            new QuotationResource($quote),
            'Quotation details retrieved successfully.'
        );
    }

    /**
     * Convert accepted quotation to Booking checkout transaction.
     */
    public function convert(string $id, Request $request, ConvertQuotationAction $action): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|uuid|exists:branches,id',
            'customer_id' => 'required|uuid|exists:users,id',
        ]);

        $booking = $action->execute(
            $id,
            $request->input('branch_id'),
            $request->input('customer_id')
        );

        return $this->successResponse(
            $booking, // Return the raw booking model/array
            'Quotation converted to Booking successfully.',
            201
        );
    }
}
