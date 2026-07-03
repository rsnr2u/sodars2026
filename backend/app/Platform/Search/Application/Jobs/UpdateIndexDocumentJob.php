<?php

declare(strict_types=1);

namespace App\Platform\Search\Application\Jobs;

use App\Platform\Search\Application\Services\IndexingService;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateIndexDocumentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $action, // 'index' or 'remove'
        protected ?string $entityClass = null,
        protected ?string $entityId = null,
        protected ?string $indexName = null
    ) {}

    public function handle(IndexingService $service): void
    {
        if ($this->action === 'index' && $this->entityClass && $this->entityId) {
            $model = $this->entityClass::find($this->entityId);
            if ($model instanceof Searchable) {
                $service->index($model);
            }
        } elseif ($this->action === 'remove' && $this->indexName && $this->entityId) {
            $service->remove($this->indexName, $this->entityId);
        }
    }
}
