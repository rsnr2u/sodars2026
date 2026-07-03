<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines;

use App\Modules\Providers\Application\DTOs\RegisterProviderData;
use App\Modules\Providers\Domain\Entities\Provider;
use Illuminate\Pipeline\Pipeline;

class RegisterProviderPipeline
{
    /**
     * Pipeline stages.
     *
     * @var array<int, string>
     */
    protected array $stages = [
        Stages\ValidateInputStage::class,
        Stages\ValidateUniquenessStage::class,
        Stages\ResolveBranchStage::class,
        Stages\CreateProviderStage::class,
        Stages\CreateAddressStage::class,
        Stages\CreateSettingsStage::class,
        Stages\CreateSubscriptionStage::class,
        Stages\CreateAdminStage::class,
        Stages\PublishEventsStage::class,
    ];

    /**
     * Run registration steps.
     */
    public function handle(RegisterProviderData $data): Provider
    {
        return app(Pipeline::class)
            ->send([
                'dto' => $data,
                'provider' => null,
                'default_branch_id' => null,
                'city_id' => null,
                'state_id' => null,
                'district_id' => null,
                'country_id' => null,
                'pincode_id' => null,
                'user' => null,
            ])
            ->through($this->stages)
            ->then(function (array $passable) {
                if ($passable['provider'] instanceof Provider) {
                    return $passable['provider'];
                }
                throw new \RuntimeException('RegisterProviderPipeline failed to return a Provider entity.');
            });
    }
}
