<?php

declare(strict_types=1);

namespace App\Core\Events;

use App\Platform\Identity\Application\Services\IdentityContext;
use App\Core\Context\TraceContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

abstract class BusinessEvent
{
    use Dispatchable;
    use SerializesModels;

    public readonly string $eventId;
    public readonly ?string $organizationId;
    public readonly ?string $branchId;
    public readonly ?string $actorId;
    public readonly CarbonImmutable $occurredAt;
    public readonly string $traceId;
    public readonly string $correlationId;
    public readonly ?string $requestId;
    public readonly ?string $sessionId;
    public readonly int $eventVersion;

    /**
     * Standard ERP business event envelope.
     */
    public function __construct(
        public readonly string $aggregateId,
        public readonly int $aggregateVersion,
        public readonly array $data,
        ?string $occurredAt = null,
        ?string $correlationId = null,
        ?string $traceId = null,
        ?string $eventId = null,
        ?string $organizationId = null,
        ?string $branchId = null,
        ?string $actorId = null,
        ?string $requestId = null,
        ?string $sessionId = null,
        public readonly array $metadata = [],
        int $eventVersion = 1
    ) {
        $this->eventVersion = $eventVersion;
        $this->eventId = $eventId ?? (string) Str::uuid();
        $this->occurredAt = $occurredAt ? CarbonImmutable::parse($occurredAt) : CarbonImmutable::now();

        // Trace Context fallback
        $this->traceId = $traceId ?? TraceContext::traceId() ?? (string) Str::uuid();
        $this->correlationId = $correlationId ?? TraceContext::correlationId() ?? (string) Str::uuid();
        $this->requestId = $requestId ?? (request()->hasHeader('X-Request-Id') ? request()->header('X-Request-Id') : null);
        $this->sessionId = $sessionId ?? (request()->hasSession() ? request()->session()->getId() : null);

        // Identity Context fallback
        $this->organizationId = $organizationId ?? $data['organization_id'] ?? IdentityContext::organizationId();
        $this->branchId = $branchId ?? $data['branch_id'] ?? IdentityContext::branchId();
        $this->actorId = $actorId ?? $data['user_id'] ?? IdentityContext::userId();
    }

    /**
     * Get the FQCN of the domain model associated with this event.
     */
    abstract public function getEntityClass(): string;
}
