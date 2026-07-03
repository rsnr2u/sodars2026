<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingRequest extends FormRequest
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
            'pricing_type' => ['required', 'string', 'in:baseline,seasonal,premium,promotional,programmatic,negotiated'],
            'rate_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'tax_inclusive' => ['sometimes', 'boolean'],
            'minimum_booking_days' => ['sometimes', 'integer', 'min:1'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
            'priority' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
