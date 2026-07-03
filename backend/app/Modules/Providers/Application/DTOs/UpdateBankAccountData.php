<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\DTOs;

use Illuminate\Http\Request;

class UpdateBankAccountData
{
    public function __construct(
        public readonly string $bankName,
        public readonly string $accountHolder,
        public readonly string $accountNumber,
        public readonly string $routingCode,
        public readonly bool $isPrimary = true
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            bankName: $request->input('bank_name'),
            accountHolder: $request->input('account_holder'),
            accountNumber: $request->input('account_number'),
            routingCode: $request->input('routing_code'),
            isPrimary: (bool) $request->input('is_primary', true)
        );
    }
}
