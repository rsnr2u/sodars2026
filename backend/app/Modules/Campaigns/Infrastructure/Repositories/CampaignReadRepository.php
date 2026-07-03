<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Infrastructure\Repositories;

use App\Modules\Campaigns\Application\DTOs\CampaignFilterData;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CampaignReadRepository implements CampaignReadRepositoryInterface
{
    public function findById(string $id): ?Campaign
    {
        return Campaign::find($id);
    }

    public function findOrFail(string $id): Campaign
    {
        return Campaign::findOrFail($id);
    }

    public function findByCode(string $code): ?Campaign
    {
        return Campaign::where('campaign_code', $code)->first();
    }

    /**
     * @return LengthAwarePaginator<Campaign>
     */
    public function paginate(CampaignFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Campaign::query()->with(['customer', 'branch']);

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->customerId) {
            $query->where('customer_id', $filters->customerId);
        }

        if ($filters->branchId) {
            $query->where('branch_id', $filters->branchId);
        }

        if ($filters->startDate) {
            $query->where('start_date', '>=', $filters->startDate);
        }

        if ($filters->endDate) {
            $query->where('end_date', '<=', $filters->endDate);
        }

        if ($filters->search) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters->search . '%')
                  ->orWhere('campaign_code', 'like', '%' . $filters->search . '%');
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * @return Collection<int, Campaign>
     */
    public function getActiveCampaigns(): Collection
    {
        return Campaign::whereIn('status', [CampaignStatus::Running->value, CampaignStatus::Scheduled->value])->get();
    }
}
