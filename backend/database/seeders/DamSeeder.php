<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Platform\DAM\Domain\Entities\Folder;
use App\Platform\DAM\Domain\Entities\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DamSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed folders
        $campaigns = Folder::create([
            'id' => (string) Str::uuid(),
            'name' => 'Campaign Creatives',
            'parent_id' => null,
        ]);

        Folder::create([
            'id' => (string) Str::uuid(),
            'name' => 'Summer Camp 2026',
            'parent_id' => $campaigns->id,
        ]);

        Folder::create([
            'id' => (string) Str::uuid(),
            'name' => 'KYC Documents',
            'parent_id' => null,
        ]);

        Folder::create([
            'id' => (string) Str::uuid(),
            'name' => 'Invoices & Receipts',
            'parent_id' => null,
        ]);

        // 2. Seed tags
        $tags = [
            'Billboard',
            'Premium',
            'ProofOfPlay',
            'InvoicePdf',
            'KYCDoc',
        ];

        foreach ($tags as $tagName) {
            Tag::create([
                'id' => (string) Str::uuid(),
                'name' => $tagName,
            ]);
        }
    }
}
