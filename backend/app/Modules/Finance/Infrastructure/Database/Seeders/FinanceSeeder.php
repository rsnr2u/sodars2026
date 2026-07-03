<?php

declare(strict_types=1);

namespace App\Modules\Finance\Infrastructure\Database\Seeders;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Finance\Application\Services\FinanceService;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function __construct(protected FinanceService $financeService) {}

    public function run(): void
    {
        $booking = Booking::first();

        if (!$booking) {
            $this->command->warn('Skipping FinanceSeeder: No Booking found.');
            return;
        }

        // Auto generate Proforma Invoice for the seeded booking
        $this->financeService->createProforma($booking);
    }
}
