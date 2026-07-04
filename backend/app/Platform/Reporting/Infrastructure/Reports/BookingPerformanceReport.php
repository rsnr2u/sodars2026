<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Bookings\Domain\Entities\Booking;

class BookingPerformanceReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'booking_performance';
    }

    public static function getParameterSchema(): array
    {
        return [
            'status' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $status = $parameters->getString('status');

        $query = Booking::query();
        if (!empty($status)) {
            $query->where('status', strtolower($status));
        }

        $bookings = $query->select(
            'id',
            'booking_code',
            'status',
            'grand_total_cents',
            'created_at'
        )->get();

        $totalCount = $bookings->count();
        $totalRevenueCents = $bookings->sum('grand_total_cents');

        $records = $bookings->map(fn($item) => $item->toArray())->toArray();

        return [
            'summary' => [
                'total_bookings' => $totalCount,
                'total_revenue_cents' => (int) $totalRevenueCents,
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Booking ID', 'Booking Code', 'Status', 'Grand Total Cents', 'Created At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['booking_code'] ?? '',
                $record['status'] ?? '',
                $record['grand_total_cents'] ?? 0,
                $record['created_at'] ?? '',
            ];
        }
        return $rows;
    }
}
