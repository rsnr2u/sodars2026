<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Entities\ProviderActivity;
use App\Modules\Providers\Domain\Enums\DocumentStatus;
use App\Modules\Providers\Domain\Events\ProviderVerified;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderWriteRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class AuditDocumentAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected ProviderWriteRepositoryInterface $providerWriteRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Audit a compliance document.
     */
    public function execute(string $providerId, string $docId, string $status, ?string $comment = null): ProviderDocument
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($providerId);

        /** @var ProviderDocument $doc */
        $doc = ProviderDocument::findOrFail($docId);

        if ($doc->provider_id !== $providerId) {
            throw new \InvalidArgumentException('Document does not belong to this provider.');
        }

        $doc->update([
            'status' => $status,
            'remarks' => $comment,
            'verified_by' => Auth::id() ? (string) Auth::id() : null,
            'verified_at' => now(),
        ]);

        $eventData = [
            'provider_id' => $providerId,
            'document_id' => $docId,
            'status' => $status,
            'remarks' => $comment,
        ];

        // 1. Record outbox
        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $providerId,
            eventName: 'provider.document.audited.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Log business timeline activity
        ProviderActivity::create([
            'provider_id' => $providerId,
            'activity_type' => $status === 'approved' ? 'DocumentApproved' : 'DocumentRejected',
            'description' => $status === 'approved' 
                ? "Document type [{$doc->document_type}] approved." 
                : "Document type [{$doc->document_type}] rejected. Reason: {$comment}",
            'causation_id' => TraceContext::causationId(),
            'correlation_id' => TraceContext::correlationId(),
            'trace_id' => TraceContext::traceId(),
            'created_by' => Auth::id() ? (string) Auth::id() : null,
        ]);

        // 3. Provider lifecycle transitions
        if ($status === DocumentStatus::Rejected->value) {
            $this->providerWriteRepo->update($providerId, ['status' => 'draft']);
        } elseif ($status === DocumentStatus::Approved->value) {
            $hasUnapproved = ProviderDocument::where('provider_id', $providerId)
                ->where('is_current', true)
                ->where('status', '!=', DocumentStatus::Approved->value)
                ->exists();

            if (!$hasUnapproved) {
                $this->providerWriteRepo->update($providerId, ['status' => 'verified']);

                $verifiedData = [
                    'provider_id' => $providerId,
                    'provider_code' => $provider->provider_code,
                    'status' => 'verified',
                ];

                $this->outboxService->record(
                    aggregateType: 'Provider',
                    aggregateId: $providerId,
                    eventName: 'provider.verified.v1',
                    data: $verifiedData,
                    eventVersion: 1,
                    schemaVersion: '1.0.0'
                );

                Event::dispatch(new ProviderVerified(
                    aggregateId: $providerId,
                    aggregateVersion: 1,
                    data: $verifiedData,
                    occurredAt: now()->toIso8601String(),
                    correlationId: TraceContext::correlationId(),
                    traceId: TraceContext::traceId(),
                    userId: Auth::id() ? (string) Auth::id() : null
                ));

                ProviderActivity::create([
                    'provider_id' => $providerId,
                    'activity_type' => 'Verified',
                    'description' => 'Provider account compliance documents verified successfully. Account is verified.',
                    'causation_id' => TraceContext::causationId(),
                    'correlation_id' => TraceContext::correlationId(),
                    'trace_id' => TraceContext::traceId(),
                    'created_by' => Auth::id() ? (string) Auth::id() : null,
                ]);
            }
        }

        return $doc;
    }
}
