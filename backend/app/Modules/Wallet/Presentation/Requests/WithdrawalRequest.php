<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_cents' => 'required|integer|min:1',
            'bank_account_details' => 'required|array',
            'bank_account_details.account_number' => 'required|string',
            'bank_account_details.bank_name' => 'required|string',
            'bank_account_details.ifsc_code' => 'required|string',
        ];
    }
}
