<?php

declare(strict_types=1);

namespace App\Platform\Audit\Application\Services;

use App\Platform\Audit\Domain\Contracts\AuditLogger;
use App\Platform\Audit\Domain\ValueObjects\AuditEnvelope;
use App\Platform\Audit\Domain\Entities\AuditEvent;
use App\Platform\Audit\Application\Registry\AuditEventRegistry;
use App\Platform\Audit\Application\Services\RiskResolver;
use App\Core\Context\TraceContext;
use App\Platform\Identity\Application\Services\IdentityContext;
use App\Platform\Identity\Domain\ValueObjects\DeviceFingerprint;
use Illuminate\Support\Str;

class AuditService implements AuditLogger
{
    /**
     * Dispatch and record an audit event from an envelope.
     */
    public function log(AuditEnvelope $envelope): AuditEvent
    {
        // 1. Resolve Category
        $category = AuditEventRegistry::resolveCategory($envelope->eventType);
        $envelope->category($category);

        // 2. Resolve Risk Level if not pre-set
        if (!$envelope->riskLevel) {
            $risk = RiskResolver::resolve($category, $envelope->eventType, $envelope->beforeSnapshot, $envelope->afterSnapshot);
            $envelope->risk($risk);
        }

        // 3. Resolve context values
        $userId = $envelope->userId ?? IdentityContext::userId();
        $actorName = $envelope->actorName ?? (IdentityContext::user()?->name ?? 'System');
        $orgId = $envelope->organizationId ?? IdentityContext::organizationId();

        $envelope->actor($userId, $actorName);
        $envelope->organization($orgId);

        // 4. Resolve client network info
        $envelope->ipAddress = $envelope->ipAddress ?? request()->ip() ?? '127.0.0.1';
        $envelope->userAgent = $envelope->userAgent ?? request()->userAgent() ?? 'Unknown';
        
        $fingerprint = DeviceFingerprint::fromUserAgent($envelope->userAgent);
        $envelope->deviceType = $envelope->deviceType ?? $fingerprint->deviceType;

        // 5. Populate trace hierarchy
        $traceId = app()->bound(TraceContext::class) ? TraceContext::traceId() : null;
        $correlationId = app()->bound(TraceContext::class) ? TraceContext::correlationId() : null;
        $requestId = request()->header('X-Request-ID') ?? (string) Str::uuid();
        
        $sessionId = null;
        if (request()->hasSession()) {
            $sessionId = request()->session()->get('login_session_id');
        }

        $envelope->correlation($traceId, $correlationId, $requestId, $sessionId);

        // 6. Persist to database
        $eventData = $envelope->toArray();
        $eventData['id'] = (string) Str::uuid();
        $eventData['created_at'] = now();

        return AuditEvent::create($eventData);
    }

    /**
     * Get paginated audit events for an organization.
     */
    public function getEventsForOrganization(string $orgId, ?string $category = null, ?string $risk = null, int $perPage = 20)
    {
        $query = AuditEvent::where('organization_id', $orgId)
            ->with('user');

        if ($category) {
            $query->where('category', $category);
        }

        if ($risk) {
            $query->where('risk_level', $risk);
        }

        return $query->orderBy('occurred_at', 'desc')->paginate($perPage);
    }

    /**
     * Get paginated audit events for a specific subject entity.
     */
    public function getEventsForEntity(string $auditableType, string $auditableId, int $perPage = 20)
    {
        return AuditEvent::where('auditable_type', $auditableType)
            ->where('auditable_id', $auditableId)
            ->with('user')
            ->orderBy('occurred_at', 'desc')
            ->paginate($perPage);
    }
}
