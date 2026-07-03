<?php

declare(strict_types=1);

namespace App\Modules\Finance\Infrastructure\Providers;

use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Modules\Finance\Domain\Repositories\InvoiceReadRepositoryInterface;
use App\Modules\Finance\Domain\Repositories\InvoiceWriteRepositoryInterface;
use App\Modules\Finance\Domain\Repositories\ProviderSettlementRepositoryInterface;
use App\Modules\Finance\Infrastructure\Repositories\InvoiceReadRepository;
use App\Modules\Finance\Infrastructure\Repositories\InvoiceWriteRepository;
use App\Modules\Finance\Infrastructure\Repositories\ProviderSettlementRepository;
use App\Modules\Finance\Infrastructure\Listeners\BookingEventListener;
use App\Modules\Finance\Presentation\Policies\InvoicePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvoiceReadRepositoryInterface::class, InvoiceReadRepository::class);
        $this->app->bind(InvoiceWriteRepositoryInterface::class, InvoiceWriteRepository::class);
        $this->app->bind(ProviderSettlementRepositoryInterface::class, ProviderSettlementRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(ProviderSettlement::class, InvoicePolicy::class);

        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');

        // Dynamic registration of Booking event listeners
        Event::subscribe(BookingEventListener::class);
    }
}
