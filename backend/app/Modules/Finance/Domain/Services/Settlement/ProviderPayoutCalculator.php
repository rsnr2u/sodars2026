<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Services\Settlement;

use App\Modules\Finance\Domain\Entities\ProviderSettlement;

class ProviderPayoutCalculator
{
    /**
     * Compute payout deduction or manual fees (such as wire fees/withholding tax TDS).
     * For V1 standard setup, we apply 1% TDS withholding rate on provider payout share.
     */
    public function calculateTds(ProviderSettlement $settlement): int
    {
        $tdsRate = 1.0; // 1% default TDS tax deduction
        return (int) round($settlement->provider_share_cents * ($tdsRate / 100));
    }
}
