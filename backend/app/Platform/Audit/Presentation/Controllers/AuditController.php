<?php

declare(strict_types=1);

namespace App\Platform\Audit\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Audit\Application\Services\AuditService;
use App\Platform\Identity\Application\Services\IdentityContext;
use App\Platform\DAM\Application\Services\DAMService;
use App\Platform\Notifications\Application\Services\NotificationService;
use App\Platform\Audit\Domain\Entities\AuditEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class AuditController extends BaseApiController
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * List paginated audit events for current organization.
     */
    public function index(Request $request): JsonResponse
    {
        $orgId = IdentityContext::organizationId();
        if (!$orgId && !$request->user()?->hasRole('super_admin')) {
            return $this->successResponse([], 'Audit events retrieved.');
        }

        $category = $request->query('category');
        $risk = $request->query('risk');
        $perPage = $this->getPerPage();

        if ($request->user()?->hasRole('super_admin') && !$orgId) {
            // Super Admin can list all audit events globally
            $query = AuditEvent::with('user');
            if ($category) {
                $query->where('category', $category);
            }
            if ($risk) {
                $query->where('risk_level', $risk);
            }
            $logs = $query->orderBy('occurred_at', 'desc')->paginate($perPage);
        } else {
            $logs = $this->auditService->getEventsForOrganization($orgId, $category, $risk, $perPage);
        }

        return $this->successResponse($logs->toArray(), 'Audit events retrieved.');
    }

    /**
     * Filter audit events by high/critical risks.
     */
    public function getHighRisks(Request $request): JsonResponse
    {
        $orgId = IdentityContext::organizationId();
        if (!$orgId && !$request->user()?->hasRole('super_admin')) {
            return $this->successResponse([], 'High risk audit events retrieved.');
        }

        $perPage = $this->getPerPage();
        $query = AuditEvent::whereIn('risk_level', ['high', 'critical'])->with('user');

        if ($orgId && !$request->user()?->hasRole('super_admin')) {
            $query->where('organization_id', $orgId);
        }

        $logs = $query->orderBy('occurred_at', 'desc')->paginate($perPage);
        return $this->successResponse($logs->toArray(), 'High risk audit events retrieved.');
    }

    /**
     * Query timeline for a specific auditable record.
     */
    public function getEntityTimeline(string $type, string $id, Request $request): JsonResponse
    {
        // Enforce subject mapping alias matching IdentityController pattern
        $subjectType = str_replace('-', '\\', $type);
        if (!class_exists($subjectType)) {
            $aliases = [
                'user' => \App\Models\User::class,
                'booking' => \App\Modules\Bookings\Domain\Entities\Booking::class,
                'inventory' => \App\Modules\Inventory\Domain\Entities\Inventory::class,
            ];
            if (isset($aliases[strtolower($type)])) {
                $subjectType = $aliases[strtolower($type)];
            } else {
                return $this->errorResponse('Invalid subject type.', null, 400);
            }
        }

        $perPage = $this->getPerPage();
        $logs = $this->auditService->getEventsForEntity($subjectType, $id, $perPage);

        // Scope by organization boundary
        $orgId = IdentityContext::organizationId();
        if ($orgId && !$request->user()?->hasRole('super_admin')) {
            foreach ($logs as $log) {
                if ($log->organization_id !== $orgId) {
                    return $this->errorResponse('Access denied.', null, 403);
                }
            }
        }

        return $this->successResponse($logs->toArray(), 'Entity timeline retrieved.');
    }

    /**
     * Export compliance logs to CSV and save in DAM system.
     */
    public function export(Request $request): JsonResponse
    {
        $orgId = IdentityContext::organizationId();
        if (!$orgId && !$request->user()?->hasRole('super_admin')) {
            return $this->errorResponse('Organization context missing.', null, 400);
        }

        // Fetch audit events to export
        $query = AuditEvent::query();
        if ($orgId && !$request->user()?->hasRole('super_admin')) {
            $query->where('organization_id', $orgId);
        }
        $events = $query->orderBy('occurred_at', 'desc')->get();

        // 1. Build CSV Content
        $headers = ['ID', 'Category', 'Event Type', 'Risk Level', 'Description', 'Actor', 'IP Address', 'Correlation ID', 'Occurred At'];
        $rows = [];
        foreach ($events as $e) {
            $rows[] = [
                $e->id,
                $e->category,
                $e->event_type,
                $e->risk_level,
                $e->description,
                $e->actor_name ?? 'System',
                $e->ip_address,
                $e->correlation_id,
                $e->occurred_at->toIso8601String(),
            ];
        }

        $csv = implode(',', $headers) . "\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v ?? '') . '"', $row)) . "\n";
        }

        // 2. Save CSV to temporary file
        $tempPath = tempnam(sys_get_temp_dir(), 'aud_');
        file_put_contents($tempPath, $csv);

        $filename = 'audit_export_' . date('Ymd_His') . '.csv';
        $uploadedFile = new UploadedFile($tempPath, $filename, 'text/csv', null, true);

        try {
            // 3. Upload to DAM system
            /** @var DAMService $dam */
            $dam = app(DAMService::class);
            $asset = $dam->upload(
                $uploadedFile,
                'Audit Export ' . date('Y-m-d H:i:s'),
                'Compliance export of audit events logs generated on demand.'
            );

            // 4. Send download notification if service exists
            if (app()->bound(NotificationService::class) && $request->user()) {
                /** @var NotificationService $notifications */
                $notifications = app(NotificationService::class);
                
                // Fetch direct asset URL
                $downloadUrl = $dam->getUrl($asset->currentVersion->file->path ?? '');

                try {
                    $notifications->send(
                        (string) $request->user()->id,
                        'audit_export_ready', // Expecting this template or fallback
                        ['download_url' => $downloadUrl, 'filename' => $filename]
                    );
                } catch (\Exception $e) {
                    // Fail-safe if notification template seed is not loaded
                }
            }

            return $this->successResponse([
                'asset_id' => $asset->id,
                'filename' => $filename,
                'download_url' => isset($downloadUrl) ? $downloadUrl : null,
            ], 'Audit log exported to DAM successfully.');
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}
