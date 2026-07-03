<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankAccountRequest extends FormRequest
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
            'bank_name' => ['required', 'string', 'max:100'],
            'account_holder' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:50'],
            'routing_code' => ['required', 'string', 'max:30'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}
