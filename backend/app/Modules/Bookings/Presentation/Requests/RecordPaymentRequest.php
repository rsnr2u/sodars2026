<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
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
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,cheque,upi,neft,rtgs'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'reference_number' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
