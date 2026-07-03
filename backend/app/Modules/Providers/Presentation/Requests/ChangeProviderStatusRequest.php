<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeProviderStatusRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:pending,verified,suspended,deactivated'],
        ];
    }
}
