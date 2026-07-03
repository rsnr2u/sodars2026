<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Infrastructure\Providers;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Repositories\BookingReadRepositoryInterface;
use App\Modules\Bookings\Domain\Repositories\BookingWriteRepositoryInterface;
use App\Modules\Bookings\Domain\Repositories\BookingPaymentRepositoryInterface;
use App\Modules\Bookings\Infrastructure\Repositories\BookingReadRepository;
use App\Modules\Bookings\Infrastructure\Repositories\BookingWriteRepository;
use App\Modules\Bookings\Infrastructure\Repositories\BookingPaymentRepository;
use App\Modules\Bookings\Presentation\Policies\BookingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BookingReadRepositoryInterface::class, BookingReadRepository::class);
        $this->app->bind(BookingWriteRepositoryInterface::class, BookingWriteRepository::class);
        $this->app->bind(BookingPaymentRepositoryInterface::class, BookingPaymentRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);

        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');

        // Register Workflow Transition Handler
        if ($this->app->bound(\App\Platform\Workflows\Infrastructure\Registry\WorkflowRegistry::class)) {
            $registry = $this->app->make(\App\Platform\Workflows\Infrastructure\Registry\WorkflowRegistry::class);
            $registry->register(
                \App\Modules\Bookings\Domain\Entities\Booking::class,
                \App\Modules\Bookings\Infrastructure\Workflows\BookingWorkflowHandler::class
            );
        }
    }
}
