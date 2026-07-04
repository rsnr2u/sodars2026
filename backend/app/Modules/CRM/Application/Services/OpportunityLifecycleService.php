<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Services;

use App\Core\Services\OutboxService;
use App\Modules\CRM\Domain\Entities\Opportunity;
use App\Modules\CRM\Domain\Events\OpportunityCreated;
use App\Modules\CRM\Domain\Events\OpportunityStageChanged;
use Illuminate\Support\Facades\Event;

class OpportunityLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    /**
     * Record opportunity creation.
     */
    public function recordCreation(Opportunity $opp): void
    {
        $eventData = [
            'opportunity_id' => $opp->id,
            'title' => $opp->title,
            'estimated_value_cents' => $opp->estimated_value_cents,
            'probability' => $opp->probability,
            'expected_value_cents' => $opp->expected_value_cents,
            'pipeline_stage_id' => $opp->pipeline_stage_id,
        ];

        $metadata = [
            'pipeline_stage_id' => $opp->pipeline_stage_id,
            'probability' => $opp->probability,
            'expected_revenue' => $opp->expected_value_cents,
            'assigned_user_id' => $opp->assigned_to,
        ];

        $this->outboxService->record(
            aggregateType: 'Opportunity',
            aggregateId: $opp->id,
            eventName: 'crm.opportunity.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new OpportunityCreated(
            aggregateId: $opp->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }

    /**
     * Record stage updates.
     */
    public function recordStageChange(Opportunity $opp, string $fromStageId, string $toStageId): void
    {
        $eventData = [
            'opportunity_id' => $opp->id,
            'title' => $opp->title,
            'from_pipeline_stage_id' => $fromStageId,
            'to_pipeline_stage_id' => $toStageId,
            'estimated_value_cents' => $opp->estimated_value_cents,
            'probability' => $opp->probability,
            'expected_value_cents' => $opp->expected_value_cents,
        ];

        $metadata = [
            'pipeline_stage_id' => $toStageId,
            'probability' => $opp->probability,
            'expected_revenue' => $opp->expected_value_cents,
            'assigned_user_id' => $opp->assigned_to,
        ];

        $this->outboxService->record(
            aggregateType: 'Opportunity',
            aggregateId: $opp->id,
            eventName: 'crm.opportunity.stage_changed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new OpportunityStageChanged(
            aggregateId: $opp->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }
}
