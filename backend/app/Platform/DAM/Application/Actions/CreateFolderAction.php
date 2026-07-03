<?php

declare(strict_types=1);

namespace App\Platform\DAM\Application\Actions;

use App\Platform\DAM\Domain\Entities\Folder;
use Illuminate\Support\Str;

class CreateFolderAction
{
    public function execute(string $name, ?string $parentId = null): Folder
    {
        if ($parentId) {
            // Verify parent exists
            Folder::findOrFail($parentId);
        }

        return Folder::create([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'parent_id' => $parentId,
        ]);
    }
}
