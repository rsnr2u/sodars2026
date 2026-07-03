<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\DTOs;

use Illuminate\Http\Request;

class CreateBranchData
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly string $timezone,
        public readonly string $currencyCode,
        public readonly int $markupPercentage,
        public readonly string $supportEmail,
        public readonly string $supportPhone,
        public readonly ?string $managerUserId = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            code: $request->input('code'),
            timezone: $request->input('timezone', 'Asia/Kolkata'),
            currencyCode: $request->input('currency_code', 'INR'),
            markupPercentage: (int) $request->input('markup_percentage', 20),
            supportEmail: $request->input('support_email'),
            supportPhone: $request->input('support_phone'),
            managerUserId: $request->input('manager_user_id')
        );
    }
}
