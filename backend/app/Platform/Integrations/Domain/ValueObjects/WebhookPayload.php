<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Domain\ValueObjects;

use Illuminate\Support\Str;

class WebhookPayload
{
    public function __construct(
        protected string $id,
        protected string $source,
        protected string $type,
        protected string $subject,
        protected string $time,
        protected array $data
    ) {}

    public static function create(string $source, string $type, string $subject, array $data): self
    {
        return new self(
            (string) Str::uuid(),
            $source,
            $type,
            $subject,
            now()->toIso8601String(),
            $data
        );
    }

    public function toArray(): array
    {
        return [
            'specversion' => '1.0',
            'id' => $this->id,
            'source' => $this->source,
            'type' => $this->type,
            'subject' => $this->subject,
            'time' => $this->time,
            'datacontenttype' => 'application/json',
            'data' => $this->data,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
