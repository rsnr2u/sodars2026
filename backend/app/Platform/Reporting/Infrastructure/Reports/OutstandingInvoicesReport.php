<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Enums\InvoiceStatus;

class OutstandingInvoicesReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'outstanding_invoices';
    }

    public static function getParameterSchema(): array
    {
        return [
            'customer_id' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $customerId = $parameters->getString('customer_id');

        $query = Invoice::query()
            ->whereNotIn('status', [
                InvoiceStatus::Paid->value,
                InvoiceStatus::Voided->value,
                InvoiceStatus::Draft->value,
            ]);

        if (!empty($customerId)) {
            $query->where('customer_id', $customerId);
        }

        $invoices = $query->orderBy('due_date', 'asc')->take(500)->get();

        $totalOutstanding = $invoices->sum('grand_total_cents');

        $records = $invoices->map(fn(Invoice $inv) => [
            'id' => $inv->id,
            'invoice_number' => $inv->invoice_number,
            'customer_id' => $inv->customer_id,
            'issue_date' => $inv->issue_date?->toDateString(),
            'due_date' => $inv->due_date?->toDateString(),
            'status' => $inv->status instanceof \BackedEnum ? $inv->status->value : (string) $inv->status,
            'grand_total_cents' => $inv->grand_total_cents,
            'currency' => $inv->currency,
        ])->toArray();

        return [
            'summary' => [
                'total_outstanding_cents' => (int) $totalOutstanding,
                'invoice_count' => $invoices->count(),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Invoice Number', 'Customer ID', 'Issue Date', 'Due Date', 'Status', 'Grand Total', 'Currency'];
    }

    public function getExportRows(array $data): array
    {
        $rows = []  ;
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['invoice_number'],
                $rec['customer_id'],
                $rec['issue_date'],
                $rec['due_date'],
                $rec['status'],
                $rec['grand_total_cents'],
                $rec['currency'],
            ];
        }
        return $rows;
    }
}
