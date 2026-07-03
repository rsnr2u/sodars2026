<?php

declare(strict_types=1);

namespace App\Modules\Branches\Infrastructure\Providers;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Repositories\BranchCoverageAreaRepositoryInterface;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Branches\Infrastructure\Repositories\BranchCoverageAreaRepository;
use App\Modules\Branches\Infrastructure\Repositories\BranchRepository;
use App\Modules\Branches\Presentation\Policies\BranchPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BranchServiceProvider extends ServiceProvider
{
    /**
     * Register bindings.
     */
    public function register(): void
    {
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(BranchCoverageAreaRepositoryInterface::class, BranchCoverageAreaRepository::class);
    }

    /**
     * Boot service provider.
     */
    public function boot(): void
    {
        Gate::policy(Branch::class, BranchPolicy::class);

        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');
    }
}
