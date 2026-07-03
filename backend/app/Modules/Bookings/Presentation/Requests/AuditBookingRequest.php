<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditBookingRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:approved,rejected,cancelled'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
