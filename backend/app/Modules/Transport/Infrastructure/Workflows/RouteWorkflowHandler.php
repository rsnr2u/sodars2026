<?php

declare(strict_types=1);

namespace App\Modules\Transport\Infrastructure\Workflows;

use App\Modules\Transport\Domain\Entities\Route;
use App\Modules\Transport\Domain\Enums\RouteStatus;
use App\Modules\Transport\Application\Services\TransportService;
use App\Platform\Workflows\Domain\Contracts\WorkflowHandler;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowContext;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowResult;

class RouteWorkflowHandler implements WorkflowHandler
{
    public function __construct(
        protected TransportService $transportService
    ) {}

    public function entityClass(): string
    {
        return Route::class;
    }

    public function workflowKey(): string
    {
        return 'route.dispatch_approval';
    }

    public function availableTransitions(object $entity): array
    {
        return ['approve', 'reject', 'request_changes'];
    }

    public function transition(
        object $entity,
        string $transition,
        WorkflowContext $context
    ): WorkflowResult {
        $route = $entity;
        $previousState = $route->status->value ?? $route->status;

        if ($transition === 'approve') {
            $this->transportService->dispatchRoute($route->id);
            $newState = RouteStatus::Dispatched->value;
        } elseif ($transition === 'reject') {
            $this->transportService->cancelRoute($route->id);
            $newState = RouteStatus::Cancelled->value;
        } elseif ($transition === 'request_changes') {
            $newState = RouteStatus::Draft->value;
            $this->transportService->changeRouteStatus($route->id, RouteStatus::Draft);
        } else {
            throw new \InvalidArgumentException("Invalid workflow transition: {$transition}");
        }

        return WorkflowResult::create(
            success: true,
            previousState: $previousState,
            newState: $newState,
            metadata: ['route_number' => $route->route_number]
        );
    }

    public function compensate(
        object $entity,
        \App\Platform\Workflows\Domain\Entities\WorkflowHistory $history,
        WorkflowContext $context
    ): WorkflowResult {
        $route = $entity;
        $previousState = $route->status->value ?? $route->status;
        $targetStatus = $history->from_state ?? RouteStatus::Draft->value;

        $this->transportService->changeRouteStatus($route->id, RouteStatus::from($targetStatus));

        return WorkflowResult::create(
            success: true,
            previousState: $previousState,
            newState: $targetStatus
        );
    }
}
