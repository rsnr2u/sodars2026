<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Services;

use App\Modules\Campaigns\Application\Actions\CreateCampaignAction;
use App\Modules\Campaigns\Application\Actions\UpdateCampaignAction;
use App\Modules\Campaigns\Application\Actions\UploadCreativeAction;
use App\Modules\Campaigns\Application\Actions\AuditCreativeAction;
use App\Modules\Campaigns\Application\Actions\UploadProofAction;
use App\Modules\Campaigns\Application\Actions\AuditProofAction;
use App\Modules\Campaigns\Application\Actions\ChangeCampaignStatusAction;
use App\Modules\Campaigns\Application\Queries\ListCampaignsQuery;
use App\Modules\Campaigns\Application\Queries\GetCampaignDetailsQuery;
use App\Modules\Campaigns\Application\Queries\CampaignDashboardQuery;
use App\Modules\Campaigns\Application\DTOs\CreateCampaignData;
use App\Modules\Campaigns\Application\DTOs\UpdateCampaignData;
use App\Modules\Campaigns\Application\DTOs\UploadCreativeData;
use App\Modules\Campaigns\Application\DTOs\UploadProofData;
use App\Modules\Campaigns\Application\DTOs\CampaignFilterData;
use App\Modules\Campaigns\Application\DTOs\CampaignDashboardDTO;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Entities\CampaignProof;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignService
{
    public function __construct(
        protected CreateCampaignAction $createAction,
        protected UpdateCampaignAction $updateAction,
        protected UploadCreativeAction $uploadCreativeAction,
        protected AuditCreativeAction $auditCreativeAction,
        protected UploadProofAction $uploadProofAction,
        protected AuditProofAction $auditProofAction,
        protected ChangeCampaignStatusAction $statusAction,
        protected ListCampaignsQuery $listQuery,
        protected GetCampaignDetailsQuery $detailsQuery,
        protected CampaignDashboardQuery $dashboardQuery
    ) {}

    public function create(CreateCampaignData $dto): Campaign
    {
        return $this->createAction->execute($dto);
    }

    public function update(string $id, UpdateCampaignData $dto): Campaign
    {
        return $this->updateAction->execute($id, $dto);
    }

    public function uploadCreative(string $campaignId, UploadCreativeData $dto): CampaignCreative
    {
        return $this->uploadCreativeAction->execute($campaignId, $dto);
    }

    public function auditCreative(string $campaignId, string $creativeId, string $status, ?string $rejectionReason = null): CampaignCreative
    {
        return $this->auditCreativeAction->execute($campaignId, $creativeId, $status, $rejectionReason);
    }

    public function uploadProof(string $campaignId, UploadProofData $dto): CampaignProof
    {
        return $this->uploadProofAction->execute($campaignId, $dto);
    }

    public function auditProof(string $campaignId, string $proofId, string $status): CampaignProof
    {
        return $this->auditProofAction->execute($campaignId, $proofId, $status);
    }

    public function changeStatus(string $id, string $status): Campaign
    {
        return $this->statusAction->execute($id, $status);
    }

    /**
     * @return LengthAwarePaginator<Campaign>
     */
    public function list(CampaignFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery->execute($filters, $perPage);
    }

    public function getDetails(string $id): Campaign
    {
        return $this->detailsQuery->execute($id);
    }

    public function getDashboard(?string $customerId = null): CampaignDashboardDTO
    {
        return $this->dashboardQuery->execute($customerId);
    }
}
