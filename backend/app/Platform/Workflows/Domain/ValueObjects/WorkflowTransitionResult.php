<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\ValueObjects;

class WorkflowTransitionResult
{
    public function __construct(
        public bool $success,
        public string $newStatus,
        public array $events = [],
        public array $messages = [],
        public array $metadata = []
    ) {}

    public static function create(
        bool $success,
        string $newStatus,
        array $events = [],
        array $messages = [],
        array $metadata = []
    ): self {
        return new self($success, $newStatus, $events, $messages, $metadata);
    }
}
