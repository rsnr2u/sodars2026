<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\ValueObjects;

class WorkflowResult
{
    public function __construct(
        public bool $success,
        public string $previousState,
        public string $newState,
        public array $events = [],
        public array $auditEntries = [],
        public array $notifications = [],
        public array $outboxMessages = [],
        public array $metadata = []
    ) {}

    public static function create(
        bool $success,
        string $previousState,
        string $newState,
        array $events = [],
        array $auditEntries = [],
        array $notifications = [],
        array $outboxMessages = [],
        array $metadata = []
    ): self {
        return new self($success, $previousState, $newState, $events, $auditEntries, $notifications, $outboxMessages, $metadata);
    }
}
