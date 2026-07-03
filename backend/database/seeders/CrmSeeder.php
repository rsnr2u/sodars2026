<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\CRM\Domain\Entities\PipelineStage;
use App\Modules\CRM\Domain\Entities\LostReason;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CrmSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pipeline Stages
        $stages = [
            ['name' => 'Qualification', 'display_order' => 1, 'probability' => 10, 'is_closed' => false, 'is_won' => false],
            ['name' => 'Proposal', 'display_order' => 2, 'probability' => 30, 'is_closed' => false, 'is_won' => false],
            ['name' => 'Negotiation', 'display_order' => 3, 'probability' => 70, 'is_closed' => false, 'is_won' => false],
            ['name' => 'Won', 'display_order' => 4, 'probability' => 100, 'is_closed' => true, 'is_won' => true],
            ['name' => 'Lost', 'display_order' => 5, 'probability' => 0, 'is_closed' => true, 'is_won' => false],
        ];

        foreach ($stages as $stage) {
            PipelineStage::firstOrCreate(
                ['name' => $stage['name']],
                [
                    'id' => (string) Str::uuid(),
                    'display_order' => $stage['display_order'],
                    'probability' => $stage['probability'],
                    'is_closed' => $stage['is_closed'],
                    'is_won' => $stage['is_won'],
                ]
            );
        }

        // 2. Lost Reasons
        $reasons = [
            ['name' => 'Price', 'description' => 'Deal cost is higher than client budget.'],
            ['name' => 'Competitor', 'description' => 'Client chose a competing advertising partner.'],
            ['name' => 'Budget', 'description' => 'Client budget was cut or removed.'],
            ['name' => 'Delayed', 'description' => 'Campaign delayed indefinitely.'],
            ['name' => 'Cancelled', 'description' => 'Marketing plans cancelled.'],
            ['name' => 'Duplicate', 'description' => 'Duplicate lead entry.'],
        ];

        foreach ($reasons as $reason) {
            LostReason::firstOrCreate(
                ['name' => $reason['name']],
                [
                    'id' => (string) Str::uuid(),
                    'description' => $reason['description'],
                ]
            );
        }
    }
}
