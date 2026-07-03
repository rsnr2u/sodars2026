<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Actions;

use App\Modules\Bookings\Application\DTOs\CreateBookingData;
use App\Modules\Bookings\Application\Pipelines\CreateBookingPipeline;
use App\Modules\Bookings\Domain\Entities\Booking;

class CreateBookingAction
{
    public function __construct(
        protected CreateBookingPipeline $pipeline
    ) {}

    public function execute(CreateBookingData $dto): Booking
    {
        return $this->pipeline->execute($dto);
    }
}
