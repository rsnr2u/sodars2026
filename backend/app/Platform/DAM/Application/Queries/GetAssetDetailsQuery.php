<?php

declare(strict_types=1);

namespace App\Platform\DAM\Application\Queries;

use App\Platform\DAM\Domain\Entities\Asset;

class GetAssetDetailsQuery
{
    public function execute(string $assetId): Asset
    {
        return Asset::with([
            'folder',
            'currentVersion.file',
            'versions.file',
            'versions.conversions.file',
            'tags',
            'collections',
            'attachments',
            'ai',
        ])->findOrFail($assetId);
    }
}
