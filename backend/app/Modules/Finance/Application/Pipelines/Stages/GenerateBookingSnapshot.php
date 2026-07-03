<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Pipelines\Stages;

use Closure;

class GenerateBookingSnapshot
{
    public function handle(array $passable, Closure $next): mixed
    {
        $booking = $passable['booking'];
        
        $customer = $booking->customer;
        $branch = $booking->branch;
        
        // Eager load items faces & providers
        $booking->load(['items.face.inventory.provider']);

        $faces = [];
        foreach ($booking->items as $item) {
            $face = $item->face;
            $faces[] = [
                'id' => $face->id,
                'display_name' => $face->display_name,
                'face_code' => $face->face_code,
                'inventory' => [
                    'id' => $face->inventory->id,
                    'display_name' => $face->inventory->display_name,
                    'inventory_code' => $face->inventory->inventory_code,
                    'category' => $face->inventory->inventory_category,
                ]
            ];
        }

        $provider = $booking->items->first()?->face?->inventory?->provider;

        // Compile complete booking snapshot JSON to keep the invoice immutable
        $snapshot = [
            'booking_number' => $booking->booking_code,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'state_id' => $customer->state_id ?? null,
            ],
            'provider' => $provider ? [
                'id' => $provider->id,
                'company_name' => $provider->company_name,
                'registration_number' => $provider->registration_number,
                'provider_code' => $provider->provider_code,
            ] : null,
            'faces' => $faces,
            'pricing' => [
                'subtotal_cents' => $passable['subtotal_cents'],
                'discount_cents' => $passable['discount_cents'],
                'tax_cents' => $passable['tax_cents'],
                'grand_total_cents' => $passable['grand_total_cents'],
            ],
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'support_email' => $branch->support_email,
                'state_id' => $branch->state_id ?? null,
            ],
            'tax_rules' => $passable['taxes_breakdown'],
        ];

        $passable['booking_snapshot'] = $snapshot;

        return $next($passable);
    }
}
