<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Models\User;
use App\Modules\Providers\Application\DTOs\AddStaffData;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use App\Modules\Providers\Domain\Entities\ProviderActivity;
use App\Modules\Providers\Domain\Events\ProviderStaffAssigned;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddStaffAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Add a staff member user registration mapped to provider business workspace.
     */
    public function execute(string $providerId, AddStaffData $data): ProviderStaff
    {
        $this->providerReadRepo->findOrFail($providerId);

        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('provider_staff');

        /** @var ProviderStaff $staff */
        $staff = ProviderStaff::create([
            'provider_id' => $providerId,
            'user_id' => $user->id,
            'is_primary' => $data->isPrimary,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $eventData = [
            'provider_id' => $providerId,
            'user_id' => $user->id,
            'email' => $data->email,
            'is_primary' => $data->isPrimary,
        ];

        // 3. Outbox
        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $providerId,
            eventName: 'provider.staff.assigned.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 4. Dispatch Domain Event
        Event::dispatch(new ProviderStaffAssigned(
            aggregateId: $providerId,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null
        ));

        // 5. Activity Timeline log
        ProviderActivity::create([
            'provider_id' => $providerId,
            'activity_type' => 'StaffAdded',
            'description' => "Invited staff member [{$data->name}] ({$data->email}).",
            'causation_id' => TraceContext::causationId(),
            'correlation_id' => TraceContext::correlationId(),
            'trace_id' => TraceContext::traceId(),
            'created_by' => Auth::id() ? (string) Auth::id() : null,
        ]);

        return $staff;
    }
}
