<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\ValueObjects;

class WorkflowContext
{
    public function __construct(
        public ?string $actorId = null,
        public ?string $organizationId = null,
        public ?string $comments = null,
        public array $metadata = [],
        public array $attachments = [],
        public ?string $traceId = null,
        public ?string $correlationId = null
    ) {}
}
