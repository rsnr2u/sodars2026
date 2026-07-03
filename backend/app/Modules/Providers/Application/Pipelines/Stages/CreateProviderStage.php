<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

use App\Modules\Providers\Domain\Entities\ProviderContact;
use App\Modules\Providers\Domain\Enums\ContactType;
use App\Modules\Providers\Domain\Repositories\ProviderWriteRepositoryInterface;
use Closure;

class CreateProviderStage
{
    public function __construct(
        protected ProviderWriteRepositoryInterface $providerRepo
    ) {}

    /**
     * Persist provider record and initial owner contact details.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        // Generate unique sequential provider code: PRV-000000
        $code = 'PRV-' . str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $provider = $this->providerRepo->create([
            'company_name' => $dto->companyName,
            'registration_number' => $dto->registrationNumber,
            'provider_code' => $code,
            'default_branch_id' => $passable['default_branch_id'],
            'status' => 'draft',
            'preferred_payout_method' => 'bank',
            'external_reference' => $dto->externalReference,
            'legacy_reference' => $dto->legacyReference,
        ]);

        // Create initial owner contact
        ProviderContact::create([
            'provider_id' => $provider->id,
            'contact_name' => $dto->contactName,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'type' => ContactType::Owner->value,
        ]);

        $passable['provider'] = $provider;

        return $next($passable);
    }
}
