<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_cents' => 'required|integer|min:1',
            'reference' => 'required|string|max:100',
            'metadata' => 'nullable|array',
        ];
    }
}
