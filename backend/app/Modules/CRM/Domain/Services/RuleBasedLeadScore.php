<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Services;

use App\Modules\CRM\Domain\Entities\Lead;

class RuleBasedLeadScore implements LeadScoreStrategy
{
    public function calculate(Lead $lead): int
    {
        $score = 0;

        // 1. Source score
        switch ($lead->source) {
            case 'referral':
                $score += 40;
                break;
            case 'walk_in':
                $score += 30;
                break;
            case 'website':
                $score += 20;
                break;
            default:
                $score += 10;
                break;
        }

        // 2. Title keyword check
        $titleLower = strtolower($lead->title);
        if (str_contains($titleLower, 'premium') || str_contains($titleLower, 'airport') || str_contains($titleLower, 'national')) {
            $score += 30;
        } else {
            $score += 10;
        }

        // 3. Status qualification status check
        if ($lead->status && $lead->status->value === 'contacted') {
            $score += 20;
        }

        // Clip to maximum 100
        return min(100, $score);
    }
}
