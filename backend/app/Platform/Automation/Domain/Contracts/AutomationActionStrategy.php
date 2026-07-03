<?php

declare(strict_types=1);

namespace App\Platform\Automation\Domain\Contracts;

use App\Platform\Automation\Domain\Entities\AutomationRule;

interface AutomationActionStrategy
{
    /**
     * Execute the strategy.
     */
    public function execute(AutomationRule $rule, array $actionParams, array $eventPayload): void;
}
