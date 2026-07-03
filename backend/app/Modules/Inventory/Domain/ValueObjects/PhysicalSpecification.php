<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PhysicalSpecification implements CastsAttributes
{
    public function __construct(
        public readonly int $widthCm = 0,
        public readonly int $heightCm = 0,
        public readonly string $orientation = 'landscape',
        public readonly bool $illuminated = false,
        public readonly ?string $material = null,
        public readonly ?string $resolution = null,
        public readonly ?string $pixelPitch = null,
        public readonly ?string $viewingDistance = null,
        public readonly ?string $powerRating = null
    ) {}

    /**
     * Cast the database JSON string to the PhysicalSpecification value object.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): self
    {
        if (empty($value)) {
            return new self();
        }

        $data = json_decode((string) $value, true) ?: [];

        return new self(
            widthCm: (int) ($data['width_cm'] ?? 0),
            heightCm: (int) ($data['height_cm'] ?? 0),
            orientation: (string) ($data['orientation'] ?? 'landscape'),
            illuminated: (bool) ($data['illuminated'] ?? false),
            material: $data['material'] ?? null,
            resolution: $data['resolution'] ?? null,
            pixelPitch: $data['pixel_pitch'] ?? null,
            viewingDistance: $data['viewing_distance'] ?? null,
            powerRating: $data['power_rating'] ?? null
        );
    }

    /**
     * Cast the PhysicalSpecification value object back to database JSON.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof self) {
            return json_encode([
                'width_cm' => $value->widthCm,
                'height_cm' => $value->heightCm,
                'orientation' => $value->orientation,
                'illuminated' => $value->illuminated,
                'material' => $value->material,
                'resolution' => $value->resolution,
                'pixel_pitch' => $value->pixelPitch,
                'viewing_distance' => $value->viewingDistance,
                'power_rating' => $value->powerRating,
            ]);
        }

        if (is_array($value)) {
            return json_encode([
                'width_cm' => (int) ($value['width_cm'] ?? 0),
                'height_cm' => (int) ($value['height_cm'] ?? 0),
                'orientation' => (string) ($value['orientation'] ?? 'landscape'),
                'illuminated' => (bool) ($value['illuminated'] ?? false),
                'material' => $value['material'] ?? null,
                'resolution' => $value['resolution'] ?? null,
                'pixel_pitch' => $value['pixel_pitch'] ?? null,
                'viewing_distance' => $value['viewing_distance'] ?? null,
                'power_rating' => $value['power_rating'] ?? null,
            ]);
        }

        return null;
    }
}
