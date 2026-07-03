<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Services;

use App\Modules\Providers\Application\DTOs\AddStaffData;
use App\Modules\Providers\Application\DTOs\ProviderDashboardDTO;
use App\Modules\Providers\Application\DTOs\ProviderFilterData;
use App\Modules\Providers\Application\DTOs\RegisterProviderData;
use App\Modules\Providers\Application\DTOs\UpdateBankAccountData;
use App\Modules\Providers\Application\DTOs\UploadDocumentData;
use App\Modules\Providers\Application\Actions\AddStaffAction;
use App\Modules\Providers\Application\Actions\AuditDocumentAction;
use App\Modules\Providers\Application\Actions\ChangeProviderStatusAction;
use App\Modules\Providers\Application\Actions\RemoveStaffAction;
use App\Modules\Providers\Application\Actions\UpdateBankAccountAction;
use App\Modules\Providers\Application\Actions\UpdateSubscriptionAction;
use App\Modules\Providers\Application\Actions\UploadDocumentAction;
use App\Modules\Providers\Application\Pipelines\RegisterProviderPipeline;
use App\Modules\Providers\Application\Queries\GetProviderDetailsQuery;
use App\Modules\Providers\Application\Queries\ListProvidersQuery;
use App\Modules\Providers\Application\Queries\ProviderDashboardQuery;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderBankAccount;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProviderService
{
    public function __construct(
        protected RegisterProviderPipeline $registerPipeline,
        protected GetProviderDetailsQuery $detailsQuery,
        protected ListProvidersQuery $listQuery,
        protected ProviderDashboardQuery $dashboardQuery,
        protected UploadDocumentAction $uploadDocAction,
        protected AuditDocumentAction $auditDocAction,
        protected UpdateBankAccountAction $bankAction,
        protected AddStaffAction $addStaffAction,
        protected RemoveStaffAction $removeStaffAction,
        protected UpdateSubscriptionAction $subAction,
        protected ChangeProviderStatusAction $statusAction
    ) {}

    /**
     * Run registration steps.
     */
    public function registerProvider(RegisterProviderData $dto): Provider
    {
        return DB::transaction(fn () => $this->registerPipeline->handle($dto));
    }

    /**
     * Get detail mapping.
     */
    public function getProviderDetails(string $id): Provider
    {
        return $this->detailsQuery->execute($id);
    }

    /**
     * List filtered records.
     */
    public function listProviders(ProviderFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery->execute($filters, $perPage);
    }

    /**
     * Get dashboard metrics DTO.
     */
    public function getProviderDashboard(string $id): ProviderDashboardDTO
    {
        return $this->dashboardQuery->execute($id);
    }

    /**
     * Add compliance document.
     */
    public function uploadDocument(string $providerId, UploadDocumentData $data): ProviderDocument
    {
        return DB::transaction(fn () => $this->uploadDocAction->execute($providerId, $data));
    }

    /**
     * Approve or reject a document.
     */
    public function auditDocument(string $providerId, string $docId, string $status, ?string $comment = null): ProviderDocument
    {
        return DB::transaction(fn () => $this->auditDocAction->execute($providerId, $docId, $status, $comment));
    }

    /**
     * Upsert payment bank parameters.
     */
    public function updateBankAccount(string $providerId, UpdateBankAccountData $data): ProviderBankAccount
    {
        return DB::transaction(fn () => $this->bankAction->execute($providerId, $data));
    }

    /**
     * Invite staff member.
     */
    public function addStaff(string $providerId, AddStaffData $data): ProviderStaff
    {
        return DB::transaction(fn () => $this->addStaffAction->execute($providerId, $data));
    }

    /**
     * Remove staff workspace access.
     */
    public function removeStaff(string $providerId, string $staffId): void
    {
        DB::transaction(fn () => $this->removeStaffAction->execute($providerId, $staffId));
    }

    /**
     * Update active subscription plans.
     */
    public function updateSubscription(string $providerId, ?string $planId, int $maxScreens, string $billingCycle): ProviderSubscription
    {
        return DB::transaction(fn () => $this->subAction->execute($providerId, $planId, $maxScreens, $billingCycle));
    }

    /**
     * Change operational status.
     */
    public function changeProviderStatus(string $providerId, string $newStatus): Provider
    {
        return DB::transaction(fn () => $this->statusAction->execute($providerId, $newStatus));
    }
}
