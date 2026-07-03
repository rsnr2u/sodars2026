<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\DTOs;

use Illuminate\Http\Request;

class InventoryFaceData
{
    /**
     * @param array<string, mixed> $physicalSpecifications
     */
    public function __construct(
        public readonly string $faceCode,
        public readonly string $displayName,
        public readonly string $facingDirection,
        public readonly int $displayOrder,
        public readonly array $physicalSpecifications,
        public readonly bool $isActive = true
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            faceCode: (string) $request->input('face_code'),
            displayName: (string) $request->input('display_name'),
            facingDirection: (string) $request->input('facing_direction'),
            displayOrder: (int) $request->input('display_order', 1),
            physicalSpecifications: $request->input('physical_specifications', []),
            isActive: (bool) $request->input('is_active', true)
        );
    }

    /**
     * Map from raw array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            faceCode: (string) ($data['face_code'] ?? ''),
            displayName: (string) ($data['display_name'] ?? ''),
            facingDirection: (string) ($data['facing_direction'] ?? 'north'),
            displayOrder: (int) ($data['display_order'] ?? 1),
            physicalSpecifications: $data['physical_specifications'] ?? [],
            isActive: (bool) ($data['is_active'] ?? true)
        );
    }
}
