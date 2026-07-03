<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\DTOs;

use Illuminate\Http\Request;

class RegisterProviderData
{
    public function __construct(
        public readonly string $companyName,
        public readonly string $registrationNumber,
        public readonly string $city,
        public readonly string $state,
        public readonly string $contactName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $password,
        public readonly ?string $pincode = null,
        public readonly ?string $externalReference = null,
        public readonly ?string $legacyReference = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            companyName: $request->input('company_name'),
            registrationNumber: $request->input('registration_number'),
            city: $request->input('city'),
            state: $request->input('state'),
            contactName: $request->input('contact_name'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            password: $request->input('password'),
            pincode: $request->input('pincode'),
            externalReference: $request->input('external_reference'),
            legacyReference: $request->input('legacy_reference')
        );
    }
}
