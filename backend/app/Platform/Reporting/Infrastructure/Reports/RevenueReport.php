<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Enums\InvoiceStatus;

class RevenueReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'revenue';
    }

    public static function getParameterSchema(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'invoice_type' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $startDate = $parameters->getString('start_date');
        $endDate = $parameters->getString('end_date');
        $invoiceType = $parameters->getString('invoice_type');

        // Only count issued or paid invoices as revenue
        $query = Invoice::query()
            ->whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::Paid->value,
                InvoiceStatus::PartiallyPaid->value,
            ]);

        if (!empty($startDate)) {
            $query->where('issue_date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->where('issue_date', '<=', $endDate);
        }
        if (!empty($invoiceType)) {
            $query->where('invoice_type', $invoiceType);
        }

        $invoices = $query->take(500)->get();

        $totalRevenueCents = $invoices->sum('grand_total_cents');
        $totalSubtotalCents = $invoices->sum('subtotal_cents');
        $totalTaxCents = $invoices->sum('tax_cents');
        $totalDiscountCents = $invoices->sum('discount_cents');

        $records = $invoices->map(fn(Invoice $inv) => [
            'id' => $inv->id,
            'invoice_number' => $inv->invoice_number,
            'issue_date' => $inv->issue_date?->toDateString(),
            'status' => $inv->status instanceof \BackedEnum ? $inv->status->value : (string) $inv->status,
            'invoice_type' => $inv->invoice_type instanceof \BackedEnum ? $inv->invoice_type->value : (string) $inv->invoice_type,
            'subtotal_cents' => $inv->subtotal_cents,
            'tax_cents' => $inv->tax_cents,
            'discount_cents' => $inv->discount_cents,
            'grand_total_cents' => $inv->grand_total_cents,
            'currency' => $inv->currency,
        ])->toArray();

        return [
            'summary' => [
                'total_revenue_cents' => (int) $totalRevenueCents,
                'total_subtotal_cents' => (int) $totalSubtotalCents,
                'total_tax_cents' => (int) $totalTaxCents,
                'total_discount_cents' => (int) $totalDiscountCents,
                'invoice_count' => $invoices->count(),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Invoice Number', 'Issue Date', 'Status', 'Type', 'Subtotal', 'Tax', 'Discount', 'Grand Total', 'Currency'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['invoice_number'],
                $rec['issue_date'],
                $rec['status'],
                $rec['invoice_type'],
                $rec['subtotal_cents'],
                $rec['tax_cents'],
                $rec['discount_cents'],
                $rec['grand_total_cents'],
                $rec['currency'],
            ];
        }
        return $rows;
    }
}
