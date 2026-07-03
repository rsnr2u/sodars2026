<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Posting;

class JournalData
{
    public function __construct(
        public readonly string $referenceNumber,
        public readonly string $narration,
        public readonly string $journalType, // manual, booking, invoice, settlement, wallet, withdrawal, adjustment, reversal
        
        // Metadata
        public readonly ?string $sourceModule = null,
        public readonly ?string $sourceId = null,
        public readonly ?string $sourceType = null,
        public readonly ?string $sourceEvent = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $branchId = null,
        public readonly ?string $postedBy = null,
        public readonly ?string $approvedBy = null,
        
        // Trace context
        public readonly ?string $traceId = null,
        public readonly ?string $correlationId = null,
        public readonly ?string $causationId = null
    ) {}
}
