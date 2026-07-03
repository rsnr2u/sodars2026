<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCampaignRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'customer_id' => ['required', 'uuid', 'exists:users,id'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'booking_id' => ['nullable', 'uuid'],
            'description' => ['nullable', 'string', 'max:1000'],
            'objectives' => ['nullable', 'array'],
            'budget_cents' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'inventory_face_ids' => ['nullable', 'array'],
            'inventory_face_ids.*' => ['required_with:inventory_face_ids', 'uuid', 'exists:inventory_faces,id'],
        ];
    }
}
