<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\Contracts;

/**
 * SSO Identity Provider placeholder.
 * Future: Google, Microsoft, Azure AD, LDAP, SAML.
 */
interface IdentityProvider
{
    public function getName(): string;

    public function authenticate(array $credentials): ?array;
}
