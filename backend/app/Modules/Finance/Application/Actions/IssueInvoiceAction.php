<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Actions;

use App\Core\Context\TraceContext;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\InvoiceActivity;
use App\Modules\Finance\Domain\Enums\InvoiceStatus;
use App\Modules\Finance\Domain\Repositories\InvoiceWriteRepositoryInterface;
use Illuminate\Support\Facades\Event;
use App\Modules\Finance\Application\Services\InvoiceLifecycleService;
use Illuminate\Support\Str;

class IssueInvoiceAction
{
    public function __construct(
        protected InvoiceWriteRepositoryInterface $writeRepo,
        protected InvoiceLifecycleService $lifecycleService
    ) {}

    public function execute(string $invoiceId): Invoice
    {
        $invoice = $this->writeRepo->update($invoiceId, [
            'status' => InvoiceStatus::Issued->value,
            'issue_date' => now()->toDateString(),
        ]);

        // Lifecycle Service event trigger
        $this->lifecycleService->recordIssue($invoice);

        // Activity
        InvoiceActivity::create([
            'id' => (string) Str::uuid(),
            'invoice_id' => $invoice->id,
            'performed_by' => auth()->id() ?? $invoice->customer_id,
            'action' => 'Issued',
            'description' => "Invoice #{$invoice->invoice_number} transitioned from draft to issued.",
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
        ]);

        return $invoice;
    }
}
