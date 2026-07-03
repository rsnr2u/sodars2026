<?php

declare(strict_types=1);

namespace App\Platform\Search\Application\Jobs;

use App\Platform\Search\Application\Services\IndexingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebuildIndexJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $indexName
    ) {}

    public function handle(IndexingService $service): void
    {
        $service->rebuildIndex($this->indexName);
    }
}
