<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'objectives' => ['nullable', 'array'],
            'budget_cents' => ['nullable', 'integer', 'min:0'],
            'booking_id' => ['nullable', 'uuid'],
        ];
    }
}
