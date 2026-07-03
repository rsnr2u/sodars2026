<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Governed by Policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:branches,name'],
            'code' => ['required', 'string', 'max:20', 'unique:branches,code'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'markup_percentage' => ['nullable', 'integer', 'between:0,20'],
            'support_email' => ['required', 'email', 'max:100'],
            'support_phone' => ['required', 'string', 'max:20'],
            'manager_user_id' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }
}
