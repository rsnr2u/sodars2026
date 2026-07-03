<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
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
            'display_name' => ['sometimes', 'string', 'max:200'],
            'inventory_category' => ['sometimes', 'string', 'in:Static,Digital,Transit,Street Furniture,Ambient'],
            'inventory_type' => ['sometimes', 'string', 'max:100'],
            'ownership_type' => ['sometimes', 'string', 'in:owned,leased,partnership,franchise'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'normalized_address' => ['sometimes', 'string', 'max:500'],
            'search_keywords' => ['nullable', 'string', 'max:500'],
            'marketplace_enabled' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'accepts_programmatic_booking' => ['sometimes', 'boolean'],
            'visibility' => ['sometimes', 'string', 'in:public,private,unlisted'],
            'capabilities' => ['nullable', 'array'],
            'ai_scores' => ['nullable', 'array'],
        ];
    }
}
