<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Events;

use App\Core\Events\BusinessEvent;

abstract class AbstractWalletEvent extends BusinessEvent
{
    public function getEntityClass(): string
    {
        return \App\Modules\Wallet\Domain\Entities\Wallet::class;
    }
}
