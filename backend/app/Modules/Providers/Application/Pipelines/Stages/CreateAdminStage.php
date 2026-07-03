<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

use App\Models\User;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminStage
{
    /**
     * Create login credentials and register Spatie role + provider staff mapping.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $provider = $passable['provider'];

        // Create User account credentials
        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $dto->contactName,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'email_verified_at' => now(),
        ]);

        // Assign Spatie Role
        $user->assignRole('provider_admin');

        // Map as primary staff member
        ProviderStaff::create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'is_primary' => true,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $passable['user'] = $user;

        return $next($passable);
    }
}
