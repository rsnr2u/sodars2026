<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines;

use App\Modules\Bookings\Application\DTOs\CreateBookingData;
use App\Modules\Bookings\Domain\Entities\Booking;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class CreateBookingPipeline
{
    /** @var array<int, string> */
    protected array $stages = [
        Stages\ValidateInput::class,
        Stages\ResolveCustomer::class,
        Stages\ResolveBranch::class,
        Stages\ResolveCampaign::class,
        Stages\ValidateBookingAggregate::class,
        Stages\ResolvePricing::class,
        Stages\CalculateFinancialSummary::class,
        Stages\CreateBooking::class,
        Stages\ReserveAvailability::class,
        Stages\GeneratePricingSnapshot::class,
        Stages\PublishEvents::class,
    ];

    public function __construct(
        protected Pipeline $pipeline
    ) {}

    public function execute(CreateBookingData $dto): Booking
    {
        return DB::transaction(function () use ($dto) {
            $passable = [
                'dto' => $dto,
                'customer' => null,
                'branch' => null,
                'campaign' => null,
                'prices' => [], // raw array of resolved face prices
                'financial_summary' => null, // FinancialSummary VO
                'booking' => null,
                'items' => [],
            ];

            return $this->pipeline->send($passable)
                ->through($this->stages)
                ->then(function ($passable) {
                    return $passable['booking'];
                });
        });
    }
}
