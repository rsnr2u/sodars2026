<?php

declare(strict_types=1);

namespace App\Platform\Audit\Domain\Contracts;

use App\Platform\Audit\Domain\ValueObjects\AuditEnvelope;
use App\Platform\Audit\Domain\Entities\AuditEvent;

interface AuditLogger
{
    /**
     * Dispatch and record an audit event from an envelope.
     */
    public function log(AuditEnvelope $envelope): AuditEvent;
}
