<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Infrastructure\Providers;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Presentation\Policies\WalletPolicy;
use App\Modules\Wallet\Infrastructure\Listeners\SettlementListener;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class WalletServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind reports
        $this->app->singleton(\App\Platform\Accounting\Reports\TrialBalance::class);
    }

    public function boot(): void
    {
        Gate::policy(Wallet::class, WalletPolicy::class);

        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');

        // Wire Settlement Paid listener
        Event::subscribe(SettlementListener::class);
    }
}
