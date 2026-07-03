<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:200'],
            'provider_id' => ['required', 'uuid', 'exists:providers,id'],
            'inventory_category' => ['required', 'string', 'in:Static,Digital,Transit,Street Furniture,Ambient'],
            'inventory_type' => ['required', 'string', 'max:100'],
            'ownership_type' => ['required', 'string', 'in:owned,leased,partnership,franchise'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'normalized_address' => ['required', 'string', 'max:500'],
            'search_keywords' => ['nullable', 'string', 'max:500'],
            'capabilities' => ['nullable', 'array'],
            'ai_scores' => ['nullable', 'array'],

            // Faces (optional, default face auto-created)
            'faces' => ['nullable', 'array'],
            'faces.*.face_code' => ['required_with:faces', 'string', 'max:50'],
            'faces.*.display_name' => ['required_with:faces', 'string', 'max:100'],
            'faces.*.facing_direction' => ['required_with:faces', 'string', 'in:north,south,east,west,northeast,northwest,southeast,southwest,omnidirectional'],
            'faces.*.display_order' => ['nullable', 'integer', 'min:1'],
            'faces.*.physical_specifications' => ['nullable', 'array'],

            // Pricing (optional, baseline auto-created)
            'pricing' => ['nullable', 'array'],
            'pricing.*.pricing_type' => ['required_with:pricing', 'string'],
            'pricing.*.rate_cents' => ['required_with:pricing', 'integer', 'min:0'],
            'pricing.*.currency' => ['nullable', 'string', 'size:3'],
            'pricing.*.tax_inclusive' => ['nullable', 'boolean'],
            'pricing.*.minimum_booking_days' => ['nullable', 'integer', 'min:1'],
            'pricing.*.effective_from' => ['nullable', 'date'],
            'pricing.*.effective_to' => ['nullable', 'date', 'after:pricing.*.effective_from'],
            'pricing.*.priority' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
