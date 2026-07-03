<?php

declare(strict_types=1);

namespace App\Modules\Finance\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordAdjustmentRequest extends FormRequest
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
            'adjustment_type' => ['required', 'string', 'in:credit,debit'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
