<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignBranchMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}
