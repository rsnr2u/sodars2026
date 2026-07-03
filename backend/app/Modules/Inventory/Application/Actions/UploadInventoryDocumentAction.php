<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Inventory\Application\DTOs\UploadDocumentData;
use App\Modules\Inventory\Domain\Entities\InventoryDocument;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;
use App\Modules\Inventory\Domain\Events\InventoryMediaUploaded;
use App\Modules\Inventory\Domain\Repositories\InventoryReadRepositoryInterface;
use App\Platform\Shared\Domain\Entities\MediaLibrary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class UploadInventoryDocumentAction
{
    public function __construct(
        protected InventoryReadRepositoryInterface $readRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Upload and polymorphic map a compliance document to an inventory structure.
     */
    public function execute(string $inventoryId, UploadDocumentData $data): InventoryDocument
    {
        return DB::transaction(function () use ($inventoryId, $data) {
            $inventory = $this->readRepo->findOrFail($inventoryId);

            // 1. Create document entry
            $doc = InventoryDocument::create([
                'inventory_id' => $inventoryId,
                'document_type' => $data->documentType,
                'status' => 'pending',
            ]);

            // 2. Map file polymorphic reference to MediaLibrary
            MediaLibrary::create([
                'file_name' => basename($data->filePath),
                'file_path' => $data->filePath,
                'mime_type' => 'application/pdf',
                'file_size_bytes' => 2048,
                'mediable_type' => InventoryDocument::class,
                'mediable_id' => $doc->id,
            ]);

            $eventData = [
                'inventory_id' => $inventoryId,
                'document_id' => $doc->id,
                'document_type' => $data->documentType,
            ];

            // 3. Record outbox
            $this->outboxService->record(
                aggregateType: 'Inventory',
                aggregateId: $inventoryId,
                eventName: 'inventory.media.uploaded.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // 4. Dispatch event
            Event::dispatch(new InventoryMediaUploaded(
                aggregateId: $inventoryId,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) \Illuminate\Support\Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) \Illuminate\Support\Str::uuid()
            ));

            // 5. Activity log
            InventoryActivity::create([
                'inventory_id' => $inventoryId,
                'performed_by' => auth()->id(),
                'event_name' => 'inventory.media.uploaded.v1',
                'action' => 'DocumentUploaded',
                'old_values' => null,
                'new_values' => $doc->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId(),
            ]);

            return $doc;
        });
    }
}
