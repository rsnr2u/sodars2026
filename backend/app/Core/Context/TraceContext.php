<?php

declare(strict_types=1);

namespace App\Core\Context;

use Illuminate\Support\Str;

class TraceContext
{
    private string $correlationId;
    private string $traceId;
    private ?string $causationId = null;

    public function __construct()
    {
        $this->correlationId = (string) Str::uuid();
        $this->traceId = (string) Str::uuid();
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function setCorrelationId(string $id): void
    {
        $this->correlationId = $id;
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    public function setTraceId(string $id): void
    {
        $this->traceId = $id;
    }

    public function getCausationId(): ?string
    {
        return $this->causationId;
    }

    public function setCausationId(?string $id): void
    {
        $this->causationId = $id;
    }

    /**
     * Helper static accessors proxying to the request-scoped container singleton.
     */
    public static function correlationId(): string
    {
        return app(self::class)->getCorrelationId();
    }

    public static function traceId(): string
    {
        return app(self::class)->getTraceId();
    }

    public static function causationId(): ?string
    {
        return app(self::class)->getCausationId();
    }
}
