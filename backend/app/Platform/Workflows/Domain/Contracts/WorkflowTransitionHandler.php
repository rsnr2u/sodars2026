<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Contracts;

use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowTransitionResult;

interface WorkflowTransitionHandler
{
    public function approve(WorkflowInstance $instance): WorkflowTransitionResult;

    public function reject(WorkflowInstance $instance): WorkflowTransitionResult;

    public function requestChanges(WorkflowInstance $instance): WorkflowTransitionResult;
}
