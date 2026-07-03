<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\ValueObjects;

use Illuminate\Support\Arr;

class ReportParameters
{
    public function __construct(
        protected array $parameters = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->parameters, $key, $default);
    }

    public function getString(string $key, string $default = ''): string
    {
        return (string) $this->get($key, $default);
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    public function getBool(string $key, bool $default = false): bool
    {
        return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function getArray(string $key, array $default = []): array
    {
        return (array) $this->get($key, $default);
    }

    public function toArray(): array
    {
        return $this->parameters;
    }
}
