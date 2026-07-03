<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
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
            'customer_id' => ['required', 'uuid', 'exists:users,id'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'campaign_id' => ['nullable', 'uuid', 'exists:campaigns,id'],
            'currency' => ['nullable', 'string', 'size:3'],
            'items' => ['required', 'array'],
            'items.*.inventory_face_id' => ['required', 'uuid', 'exists:inventory_faces,id'],
            'items.*.start_date' => ['required', 'date'],
            'items.*.end_date' => ['required', 'date', 'after_or_equal:items.*.start_date'],
            'items.*.daily_frequency' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
