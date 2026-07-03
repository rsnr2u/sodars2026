<?php

declare(strict_types=1);

namespace App\Modules\Finance\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Finance\Application\DTOs\InvoiceFilterData;
use App\Modules\Finance\Application\Services\FinanceService;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Presentation\Requests\RecordInvoicePaymentRequest;
use App\Modules\Finance\Presentation\Requests\RecordAdjustmentRequest;
use App\Modules\Finance\Presentation\Resources\InvoiceResource;
use App\Modules\Finance\Presentation\Resources\InvoiceDetailResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends BaseApiController
{
    public function __construct(protected FinanceService $financeService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Invoice::class);

        $filters = InvoiceFilterData::fromRequest($request);
        $invoices = $this->financeService->listInvoices($filters, (int) $request->query('per_page', 15));

        return $this->successResponse(
            InvoiceResource::collection($invoices)->response()->getData(true),
            'Invoices retrieved successfully.'
        );
    }

    public function show(string $id): JsonResponse
    {
        $invoice = $this->financeService->getInvoiceDetails($id);
        Gate::authorize('view', $invoice);

        $invoice->load(['customer', 'branch', 'items', 'adjustments', 'taxes', 'activities']);

        return $this->successResponse(
            new InvoiceDetailResource($invoice),
            'Invoice detail retrieved successfully.'
        );
    }

    public function issue(string $id): JsonResponse
    {
        $invoice = $this->financeService->getInvoiceDetails($id);
        Gate::authorize('update', $invoice);

        $updated = $this->financeService->issue($id);

        return $this->successResponse(
            new InvoiceResource($updated),
            'Invoice issued successfully.'
        );
    }

    public function recordPayment(string $id, RecordInvoicePaymentRequest $request): JsonResponse
    {
        $invoice = $this->financeService->getInvoiceDetails($id);
        Gate::authorize('update', $invoice);

        $updated = $this->financeService->recordPayment(
            $id,
            (int) $request->input('amount_cents'),
            $request->input('payment_method'),
            $request->input('reference_number')
        );

        return $this->successResponse(
            new InvoiceResource($updated),
            'Invoice payment recorded successfully.'
        );
    }

    public function recordAdjustment(string $id, RecordAdjustmentRequest $request): JsonResponse
    {
        $invoice = $this->financeService->getInvoiceDetails($id);
        Gate::authorize('update', $invoice);

        $adjustment = $this->financeService->recordAdjustment(
            $id,
            $request->input('adjustment_type'),
            (int) $request->input('amount_cents'),
            $request->input('reason')
        );

        return $this->successResponse(
            $adjustment->toArray(),
            'Adjustment recorded successfully.'
        );
    }

    public function recognizeRevenue(Request $request): JsonResponse
    {
        Gate::authorize('update', Invoice::class);

        $date = $request->input('as_of_date', now()->toDateString());
        $entries = $this->financeService->recognizeRevenue($date);

        return $this->successResponse(
            count($entries),
            'Revenue recognition processed successfully for date ' . $date
        );
    }

    public function getRevenueAnalytics(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Invoice::class);

        $bookingId = $request->query('booking_id');
        $analytics = $this->financeService->getRevenueAnalytics($bookingId);

        return $this->successResponse(
            $analytics,
            'Revenue recognition ledger analytics computed successfully.'
        );
    }
}
