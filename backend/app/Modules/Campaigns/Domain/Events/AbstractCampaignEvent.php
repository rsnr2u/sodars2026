<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Events;

use App\Core\Events\BusinessEvent;

abstract class AbstractCampaignEvent extends BusinessEvent
{
    public function getEntityClass(): string
    {
        return \App\Modules\Campaigns\Domain\Entities\Campaign::class;
    }
}
