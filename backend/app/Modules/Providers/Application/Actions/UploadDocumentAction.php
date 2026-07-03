<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Providers\Application\DTOs\UploadDocumentData;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Entities\ProviderActivity;
use App\Modules\Providers\Domain\Enums\DocumentStatus;
use App\Modules\Providers\Domain\Events\ProviderDocumentUploaded;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderWriteRepositoryInterface;
use App\Platform\Shared\Domain\Entities\MediaLibrary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class UploadDocumentAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected ProviderWriteRepositoryInterface $providerWriteRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Upload a compliance document.
     */
    public function execute(string $providerId, UploadDocumentData $data): ProviderDocument
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($providerId);

        // Mark any previous document of the same type as not current
        ProviderDocument::where('provider_id', $providerId)
            ->where('document_type', $data->documentType)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        $latestVersion = ProviderDocument::where('provider_id', $providerId)
            ->where('document_type', $data->documentType)
            ->max('version') ?? 0;

        /** @var ProviderDocument $doc */
        $doc = ProviderDocument::create([
            'provider_id' => $providerId,
            'document_type' => $data->documentType,
            'status' => DocumentStatus::Pending->value,
            'version' => $latestVersion + 1,
            'is_current' => true,
        ]);

        MediaLibrary::create([
            'file_name' => basename($data->filePath),
            'file_path' => $data->filePath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'mediable_type' => ProviderDocument::class,
            'mediable_id' => $doc->id,
        ]);

        if ($provider->status->value === 'draft') {
            $this->providerWriteRepo->update($providerId, ['status' => 'pending']);
        }

        $eventData = [
            'provider_id' => $providerId,
            'document_id' => $doc->id,
            'document_type' => $data->documentType,
            'version' => $doc->version,
        ];

        // 1. Outbox
        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $providerId,
            eventName: 'provider.document.uploaded.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Event
        Event::dispatch(new ProviderDocumentUploaded(
            aggregateId: $providerId,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null
        ));

        // 3. Activity Timeline
        ProviderActivity::create([
            'provider_id' => $providerId,
            'activity_type' => 'DocumentUploaded',
            'description' => "Uploaded compliance document type [{$data->documentType}] (Version {$doc->version}).",
            'causation_id' => TraceContext::causationId(),
            'correlation_id' => TraceContext::correlationId(),
            'trace_id' => TraceContext::traceId(),
            'created_by' => Auth::id() ? (string) Auth::id() : null,
        ]);

        return $doc;
    }
}
