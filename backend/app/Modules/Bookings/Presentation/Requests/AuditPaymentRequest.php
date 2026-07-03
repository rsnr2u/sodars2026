<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditPaymentRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:verified,failed'],
        ];
    }
}
