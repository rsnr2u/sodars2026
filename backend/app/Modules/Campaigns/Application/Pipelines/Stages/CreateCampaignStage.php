<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Pipelines\Stages;

use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Platform\Identity\Application\Services\IdentityContext;
use Closure;
use Illuminate\Support\Str;

class CreateCampaignStage
{
    public function __construct(
        protected \App\Platform\Identifiers\CampaignNumberGenerator $generator
    ) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        // Determine campaign status
        // By default, starts as Draft. Can switch to ArtworkPending if creatives are not uploaded.
        $status = CampaignStatus::Draft->value;

        $campaignCode = $this->generator->generate();

        $campaign = Campaign::create([
            'id' => (string) Str::uuid(),
            'organization_id' => IdentityContext::organizationId(),
            'booking_id' => $dto->bookingId,
            'customer_id' => $dto->customerId,
            'branch_id' => $dto->branchId,
            'campaign_code' => $campaignCode,
            'name' => $dto->name,
            'description' => $dto->description,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'status' => $status,
            'objectives' => $dto->objectives,
            'budget_cents' => $dto->budgetCents,
            'currency' => $dto->currency,
        ]);

        $passable['campaign'] = $campaign;
        $passable['campaign_code'] = $campaignCode;

        return $next($passable);
    }
}
