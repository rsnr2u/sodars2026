<?php

declare(strict_types=1);

namespace App\Modules\CRM\Infrastructure\Providers;

use App\Modules\CRM\Domain\Entities\Lead;
use App\Modules\CRM\Domain\Entities\Quotation;
use App\Modules\CRM\Presentation\Policies\LeadPolicy;
use App\Modules\CRM\Presentation\Policies\QuotationPolicy;
use App\Modules\CRM\Domain\Services\LeadScoreStrategy;
use App\Modules\CRM\Domain\Services\RuleBasedLeadScore;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LeadScoreStrategy::class, RuleBasedLeadScore::class);
    }

    public function boot(): void
    {
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Quotation::class, QuotationPolicy::class);

        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');
    }
}
