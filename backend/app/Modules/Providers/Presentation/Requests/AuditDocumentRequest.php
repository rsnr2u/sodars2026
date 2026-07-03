<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditDocumentRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:approved,rejected'],
            'remarks' => ['required_if:status,rejected', 'nullable', 'string'],
        ];
    }
}
