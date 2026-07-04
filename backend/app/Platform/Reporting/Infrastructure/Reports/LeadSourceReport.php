<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\CRM\Domain\Entities\Lead;

class LeadSourceReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'lead_source';
    }

    public static function getParameterSchema(): array
    {
        return [
            'source' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $source = $parameters->getString('source');

        $query = Lead::query();
        if (!empty($source)) {
            $query->where('source', $source);
        }

        $totalLeads = $query->count();

        $records = $query->take(100)->get()->map(fn($l) => [
            'id' => $l->id,
            'title' => $l->title,
            'source' => $l->source,
            'status' => $l->status instanceof \BackedEnum ? $l->status->value : (string) $l->status,
            'lead_score' => $l->lead_score,
            'created_at' => $l->created_at?->toIso8601String(),
        ])->toArray();

        return [
            'summary' => [
                'total_leads' => $totalLeads,
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Title', 'Source', 'Status', 'Lead Score', 'Created At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['id'],
                $rec['title'],
                $rec['source'],
                $rec['status'],
                (string) $rec['lead_score'],
                $rec['created_at'],
            ];
        }
        return $rows;
    }
}
