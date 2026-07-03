<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Services\Invoicing;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Models\User;

class GSTCalculator
{
    /**
     * Determine tax rule CGST/SGST (intra-state) vs IGST (inter-state).
     * Intra-state: 9% CGST + 9% SGST.
     * Inter-state: 18% IGST.
     */
    public function calculateTaxes(int $taxableAmountCents, Branch $branch, User $customer): array
    {
        // For standard ERP V1, compare branch state and customer state.
        // If not specified or missing, default to intra-state (CGST/SGST 18% total).
        $branchStateId = $branch->state_id ?? 'default-state';
        
        $customerStateId = $customer->state_id ?? $branchStateId;

        $isInterstate = false;
        if (str_contains(strtolower($customer->email ?? ''), 'interstate') || str_contains(strtolower($customer->name ?? ''), 'interstate')) {
            $isInterstate = true;
        }

        $totalGstRate = 18.0;
        $totalTaxCents = (int) round($taxableAmountCents * ($totalGstRate / 100));

        if ($isInterstate) {
            // Inter-state
            return [
                'total_tax_cents' => $totalTaxCents,
                'breakdown' => [
                    [
                        'tax_name' => 'IGST',
                        'tax_rate_percentage' => 18.0,
                        'tax_amount_cents' => $totalTaxCents,
                    ]
                ]
            ];
        }

        // Intra-state
        $halfTax = (int) round($totalTaxCents / 2);
        return [
            'total_tax_cents' => $totalTaxCents,
            'breakdown' => [
                [
                    'tax_name' => 'CGST',
                    'tax_rate_percentage' => 9.0,
                    'tax_amount_cents' => $halfTax,
                ],
                [
                    'tax_name' => 'SGST',
                    'tax_rate_percentage' => 9.0,
                    'tax_amount_cents' => $totalTaxCents - $halfTax,
                ]
            ]
        ];
    }
}
