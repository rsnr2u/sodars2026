<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Infrastructure\Providers;

use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignWriteRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignCreativeRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignProofRepositoryInterface;
use App\Modules\Campaigns\Infrastructure\Repositories\CampaignReadRepository;
use App\Modules\Campaigns\Infrastructure\Repositories\CampaignWriteRepository;
use App\Modules\Campaigns\Infrastructure\Repositories\CampaignCreativeRepository;
use App\Modules\Campaigns\Infrastructure\Repositories\CampaignProofRepository;
use App\Modules\Campaigns\Presentation\Policies\CampaignPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CampaignServiceProvider extends ServiceProvider
{
    /**
     * Bind repository abstractions.
     */
    public function register(): void
    {
        $this->app->bind(CampaignReadRepositoryInterface::class, CampaignReadRepository::class);
        $this->app->bind(CampaignWriteRepositoryInterface::class, CampaignWriteRepository::class);
        $this->app->bind(CampaignCreativeRepositoryInterface::class, CampaignCreativeRepository::class);
        $this->app->bind(CampaignProofRepositoryInterface::class, CampaignProofRepository::class);
    }

    /**
     * Load routes and register authorization policies.
     */
    public function boot(): void
    {
        Gate::policy(Campaign::class, CampaignPolicy::class);

        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');
    }
}
