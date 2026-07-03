<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\DTOs;

use Illuminate\Http\Request;

class UpdateBranchData
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $timezone = null,
        public readonly ?string $currencyCode = null,
        public readonly ?int $markupPercentage = null,
        public readonly ?string $supportEmail = null,
        public readonly ?string $supportPhone = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            timezone: $request->input('timezone'),
            currencyCode: $request->input('currency_code'),
            markupPercentage: $request->has('markup_percentage') ? (int) $request->input('markup_percentage') : null,
            supportEmail: $request->input('support_email'),
            supportPhone: $request->input('support_phone')
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'timezone' => $this->timezone,
            'currency_code' => $this->currencyCode,
            'markup_percentage' => $this->markupPercentage,
            'support_email' => $this->supportEmail,
            'support_phone' => $this->supportPhone,
        ], fn ($val) => $val !== null);
    }
}
