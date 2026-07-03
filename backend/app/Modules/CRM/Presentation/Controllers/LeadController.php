<?php

declare(strict_types=1);

namespace App\Modules\CRM\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\CRM\Application\Actions\CreateLeadAction;
use App\Modules\CRM\Application\Actions\QualifyLeadAction;
use App\Modules\CRM\Application\Queries\ListLeadsQuery;
use App\Modules\CRM\Domain\Entities\Lead;
use App\Modules\CRM\Presentation\Requests\CreateLeadRequest;
use App\Modules\CRM\Presentation\Resources\LeadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LeadController extends BaseApiController
{
    /**
     * List all leads.
     */
    public function index(ListLeadsQuery $query, Request $request): JsonResponse
    {
        $perPage = $this->getPerPage();
        $leads = $query->execute($perPage);

        return $this->successResponse(
            LeadResource::collection($leads)->response()->getData(true),
            'Leads retrieved successfully.'
        );
    }

    /**
     * Create a new prospective lead.
     */
    public function store(CreateLeadRequest $request, CreateLeadAction $action): JsonResponse
    {
        $lead = $action->execute($request->validated());

        return $this->successResponse(
            new LeadResource($lead),
            'Lead created successfully.',
            201
        );
    }

    /**
     * Qualify lead contact details.
     */
    public function qualify(string $id, QualifyLeadAction $action): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        Gate::authorize('update', $lead);

        $qualified = $action->execute($id);

        return $this->successResponse(
            new LeadResource($qualified),
            'Lead qualified and promoted successfully.'
        );
    }
}
