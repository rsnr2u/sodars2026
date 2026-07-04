<?php

declare(strict_types=1);

namespace App\Platform\Audit\Domain\ValueObjects;

use App\Platform\Audit\Domain\Enums\EventCategory;
use App\Platform\Audit\Domain\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Model;

class AuditEnvelope
{
    public ?string $userId = null;
    public ?string $actorName = null;
    public ?string $organizationId = null;
    public EventCategory $category = EventCategory::System;
    public string $eventType;
    public int $eventVersion = 1;
    public ?string $auditableType = null;
    public ?string $auditableId = null;
    public ?array $beforeSnapshot = null;
    public ?array $afterSnapshot = null;
    public string $description;
    public ?RiskLevel $riskLevel = null;
    public ?string $ipAddress = null;
    public ?string $userAgent = null;
    public ?string $deviceType = null;
    public ?string $traceId = null;
    public ?string $correlationId = null;
    public ?string $requestId = null;
    public ?string $sessionId = null;
    public array $metadata = [];
    public \DateTimeInterface $occurredAt;

    public function __construct(string $eventType, string $description)
    {
        $this->eventType = $eventType;
        $this->description = $description;
        $this->occurredAt = now();
    }

    public static function make(string $eventType, string $description): self
    {
        return new self($eventType, $description);
    }

    public function actor(?string $userId, ?string $actorName): self
    {
        $this->userId = $userId;
        $this->actorName = $actorName;
        return $this;
    }

    public function organization(?string $organizationId): self
    {
        $this->organizationId = $organizationId;
        return $this;
    }

    public function category(EventCategory $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function risk(RiskLevel $riskLevel): self
    {
        $this->riskLevel = $riskLevel;
        return $this;
    }

    public function subject(?Model $model): self
    {
        if ($model) {
            $this->auditableType = get_class($model);
            $this->auditableId = (string) $model->getKey();
        }
        return $this;
    }

    public function before(?array $before): self
    {
        $this->beforeSnapshot = $before;
        return $this;
    }

    public function after(?array $after): self
    {
        $this->afterSnapshot = $after;
        return $this;
    }

    public function version(int $version): self
    {
        $this->eventVersion = $version;
        return $this;
    }

    public function correlation(?string $traceId, ?string $correlationId, ?string $requestId = null, ?string $sessionId = null): self
    {
        $this->traceId = $traceId;
        $this->correlationId = $correlationId;
        $this->requestId = $requestId;
        $this->sessionId = $sessionId;
        return $this;
    }

    public function metadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    public function occurredAt(\DateTimeInterface $occurredAt): self
    {
        $this->occurredAt = $occurredAt;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'actor_name' => $this->actorName,
            'organization_id' => $this->organizationId,
            'category' => $this->category->value,
            'event_type' => $this->eventType,
            'event_version' => $this->eventVersion,
            'occurred_at' => $this->occurredAt,
            'auditable_type' => $this->auditableType,
            'auditable_id' => $this->auditableId,
            'before_snapshot' => $this->beforeSnapshot,
            'after_snapshot' => $this->afterSnapshot,
            'description' => $this->description,
            'risk_level' => $this->riskLevel ? $this->riskLevel->value : null,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'device_type' => $this->deviceType,
            'trace_id' => $this->traceId,
            'correlation_id' => $this->correlationId,
            'request_id' => $this->requestId,
            'session_id' => $this->sessionId,
            'metadata' => $this->metadata,
        ];
    }
}
