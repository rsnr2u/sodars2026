<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditProofRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:verified,rejected'],
        ];
    }
}
