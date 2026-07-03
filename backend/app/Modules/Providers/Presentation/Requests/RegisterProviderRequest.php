<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterProviderRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:150'],
            'registration_number' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'contact_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'pincode' => ['nullable', 'string', 'max:15'],
            'external_reference' => ['nullable', 'string', 'max:100'],
            'legacy_reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}
