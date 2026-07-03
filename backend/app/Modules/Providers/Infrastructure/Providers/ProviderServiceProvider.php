<?php

declare(strict_types=1);

namespace App\Modules\Providers\Infrastructure\Providers;

use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderWriteRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderDocumentRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderBankAccountRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderStaffRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderSubscriptionRepositoryInterface;
use App\Modules\Providers\Infrastructure\Repositories\ProviderReadRepository;
use App\Modules\Providers\Infrastructure\Repositories\ProviderWriteRepository;
use App\Modules\Providers\Infrastructure\Repositories\ProviderDocumentRepository;
use App\Modules\Providers\Infrastructure\Repositories\ProviderBankAccountRepository;
use App\Modules\Providers\Infrastructure\Repositories\ProviderStaffRepository;
use App\Modules\Providers\Infrastructure\Repositories\ProviderSubscriptionRepository;
use App\Modules\Providers\Presentation\Policies\ProviderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ProviderServiceProvider extends ServiceProvider
{
    /**
     * Bind repository abstractions.
     */
    public function register(): void
    {
        $this->app->bind(ProviderReadRepositoryInterface::class, ProviderReadRepository::class);
        $this->app->bind(ProviderWriteRepositoryInterface::class, ProviderWriteRepository::class);
        $this->app->bind(ProviderDocumentRepositoryInterface::class, ProviderDocumentRepository::class);
        $this->app->bind(ProviderBankAccountRepositoryInterface::class, ProviderBankAccountRepository::class);
        $this->app->bind(ProviderStaffRepositoryInterface::class, ProviderStaffRepository::class);
        $this->app->bind(ProviderSubscriptionRepositoryInterface::class, ProviderSubscriptionRepository::class);
    }

    /**
     * Load routes and register authorization policies.
     */
    public function boot(): void
    {
        Gate::policy(Provider::class, ProviderPolicy::class);

        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');
    }
}
