<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Requests;

use App\Modules\Branches\Domain\Enums\BranchStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ChangeBranchStatusRequest extends FormRequest
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
            'status' => ['required', 'string', new Enum(BranchStatus::class)],
        ];
    }
}
