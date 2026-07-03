<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

use App\Modules\Providers\Domain\Entities\ProviderSetting;
use App\Modules\Providers\Domain\ValueObjects\ProviderSettings;
use Closure;

class CreateSettingsStage
{
    /**
     * Set up default settings using value object casting.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $provider = $passable['provider'];

        ProviderSetting::create([
            'provider_id' => $provider->id,
            'settings' => new ProviderSettings(
                marketplaceEnabled: true,
                bookingNotifications: true,
                email: true,
                sms: false
            ),
        ]);

        return $next($passable);
    }
}
