<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Services;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Finance\Application\Actions\CreateInvoiceAction;
use App\Modules\Finance\Application\Actions\IssueInvoiceAction;
use App\Modules\Finance\Application\Actions\RecordInvoicePaymentAction;
use App\Modules\Finance\Application\Actions\RecordAdjustmentAction;
use App\Modules\Finance\Application\Actions\GenerateSettlementAction;
use App\Modules\Finance\Application\Actions\RecognizeRevenueJobAction;
use App\Modules\Finance\Application\Queries\ListInvoicesQuery;
use App\Modules\Finance\Application\Queries\GetInvoiceDetailsQuery;
use App\Modules\Finance\Application\Queries\ListSettlementsQuery;
use App\Modules\Finance\Application\Queries\GetRevenueAnalyticsQuery;
use App\Modules\Finance\Application\DTOs\InvoiceFilterData;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\InvoiceAdjustment;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use Illuminate\Pagination\LengthAwarePaginator;

class FinanceService
{
    public function __construct(
        protected CreateInvoiceAction $createAction,
        protected IssueInvoiceAction $issueAction,
        protected RecordInvoicePaymentAction $paymentAction,
        protected RecordAdjustmentAction $adjustmentAction,
        protected GenerateSettlementAction $settlementAction,
        protected RecognizeRevenueJobAction $revenueJob,
        protected ListInvoicesQuery $listInvoicesQuery,
        protected GetInvoiceDetailsQuery $getInvoiceQuery,
        protected ListSettlementsQuery $listSettlementsQuery,
        protected GetRevenueAnalyticsQuery $analyticsQuery
    ) {}

    public function createProforma(Booking $booking): Invoice
    {
        return $this->createAction->execute($booking, 'proforma_invoice');
    }

    public function createTaxInvoice(Booking $booking): Invoice
    {
        return $this->createAction->execute($booking, 'tax_invoice');
    }

    public function issue(string $invoiceId): Invoice
    {
        return $this->issueAction->execute($invoiceId);
    }

    public function recordPayment(string $invoiceId, int $amount, string $method, string $reference): Invoice
    {
        return $this->paymentAction->execute($invoiceId, $amount, $method, $reference);
    }

    public function recordAdjustment(string $invoiceId, string $type, int $amount, string $reason): InvoiceAdjustment
    {
        return $this->adjustmentAction->execute($invoiceId, $type, $amount, $reason);
    }

    public function generateSettlement(string $bookingId, string $invoiceId): ProviderSettlement
    {
        return $this->settlementAction->execute($bookingId, $invoiceId);
    }

    public function recognizeRevenue(string $asOfDate): array
    {
        return $this->revenueJob->execute($asOfDate);
    }

    /**
     * @return LengthAwarePaginator<Invoice>
     */
    public function listInvoices(InvoiceFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listInvoicesQuery->execute($filters, $perPage);
    }

    public function getInvoiceDetails(string $id): Invoice
    {
        return $this->getInvoiceQuery->execute($id);
    }

    /**
     * @return LengthAwarePaginator<ProviderSettlement>
     */
    public function listSettlements(?string $providerId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listSettlementsQuery->execute($providerId, $perPage);
    }

    public function getRevenueAnalytics(?string $bookingId = null): array
    {
        return $this->analyticsQuery->execute($bookingId);
    }
}
