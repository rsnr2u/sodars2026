<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_wallet_id' => 'required|uuid|exists:wallets,id',
            'amount_cents' => 'required|integer|min:1',
            'reference' => 'required|string|max:100',
        ];
    }
}
