<?php

declare(strict_types=1);

namespace App\Platform\Identity\Application\Services;

use App\Platform\Identity\Domain\Entities\ActivityLog;
use App\Platform\Identity\Domain\Enums\ActivityType;
use App\Core\Context\TraceContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityService
{
    /**
     * Record an audit activity log entry.
     */
    public static function record(
        ActivityType $type,
        string $description,
        ?Model $subject = null,
        ?array $properties = null,
        ?string $userId = null,
        ?string $organizationId = null
    ): ActivityLog {
        $userId = $userId ?? IdentityContext::userId();
        $organizationId = $organizationId ?? IdentityContext::organizationId();

        return ActivityLog::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'activity_type' => $type->value,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? (string) $subject->getKey() : null,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'correlation_id' => TraceContext::correlationId(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get paginated logs for an organization.
     */
    public function getLogsForOrganization(string $organizationId, int $perPage = 20)
    {
        return ActivityLog::where('organization_id', $organizationId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get paginated logs for a user.
     */
    public function getLogsForUser(string $userId, int $perPage = 20)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get paginated logs for a specific subject entity.
     */
    public function getLogsForEntity(string $subjectType, string $subjectId, int $perPage = 20)
    {
        return ActivityLog::where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
