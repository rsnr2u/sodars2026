<?php

declare(strict_types=1);

namespace App\Platform\Audit\Infrastructure\Traits;

use App\Platform\Audit\Domain\Contracts\AuditLogger;
use App\Platform\Audit\Domain\ValueObjects\AuditEnvelope;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            self::logModelEvent($model, 'model.created', 'Created record');
        });

        static::updated(function (Model $model) {
            self::logModelEvent($model, 'model.updated', 'Updated record');
        });

        static::deleted(function (Model $model) {
            self::logModelEvent($model, 'model.deleted', 'Deleted record');
        });
    }

    protected static function logModelEvent(Model $model, string $eventType, string $actionName): void
    {
        // Resolve logger
        if (!app()->bound(AuditLogger::class)) {
            return;
        }

        /** @var AuditLogger $logger */
        $logger = app(AuditLogger::class);

        // Capture dirty/original changes
        $before = null;
        $after = null;

        $only = property_exists($model, 'auditOnly') ? $model->auditOnly : [];
        $exclude = property_exists($model, 'auditExclude') ? $model->auditExclude : [
            'password',
            'remember_token',
            'secret_token',
            'secret',
            'api_key',
        ];

        if ($eventType === 'model.created') {
            $after = self::filterAuditAttributes($model->getAttributes(), $only, $exclude);
        } elseif ($eventType === 'model.deleted') {
            $before = self::filterAuditAttributes($model->getAttributes(), $only, $exclude);
        } elseif ($eventType === 'model.updated') {
            $changes = $model->getChanges();
            if (empty($changes)) {
                return;
            }

            $beforeValues = [];
            $afterValues = [];

            foreach ($changes as $key => $val) {
                $beforeValues[$key] = $model->getOriginal($key);
                $afterValues[$key] = $val;
            }

            $before = self::filterAuditAttributes($beforeValues, $only, $exclude);
            $after = self::filterAuditAttributes($afterValues, $only, $exclude);

            // If changes are fully excluded, don't write log
            if (empty($before) && empty($after)) {
                return;
            }
        }

        $className = class_basename($model);
        $description = "{$actionName} on {$className} (ID: {$model->getKey()})";

        $envelope = AuditEnvelope::make($eventType, $description)
            ->subject($model)
            ->before($before)
            ->after($after);

        $logger->log($envelope);
    }

    protected static function filterAuditAttributes(array $attributes, array $only, array $exclude): array
    {
        $filtered = [];

        foreach ($attributes as $key => $val) {
            // Apply only list if populated
            if (!empty($only) && !in_array($key, $only, true)) {
                continue;
            }

            // Apply exclude list
            if (in_array($key, $exclude, true)) {
                continue;
            }

            $filtered[$key] = $val;
        }

        return $filtered;
    }
}
