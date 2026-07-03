<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines\Stages;

use App\Modules\Campaigns\Domain\Entities\Campaign;
use Closure;
use Illuminate\Validation\ValidationException;

class ResolveCampaign
{
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        if ($dto->campaignId) {
            $campaign = Campaign::find($dto->campaignId);
            if (!$campaign) {
                throw ValidationException::withMessages([
                    'campaign_id' => ['Target campaign not found.'],
                ]);
            }
            $passable['campaign'] = $campaign;
        }

        return $next($passable);
    }
}
