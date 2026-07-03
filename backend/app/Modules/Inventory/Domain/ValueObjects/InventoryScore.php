<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class InventoryScore implements CastsAttributes
{
    public function __construct(
        public readonly float $traffic = 0.0,
        public readonly float $visibility = 0.0,
        public readonly float $audience = 0.0,
        public readonly float $quality = 0.0,
        public readonly float $competition = 0.0,
        public readonly float $overall = 0.0,
        public readonly float $confidence = 0.0,
        public readonly ?string $generatedAt = null
    ) {}

    /**
     * Cast JSON scores data.
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
            traffic: (float) ($data['traffic'] ?? 0.0),
            visibility: (float) ($data['visibility'] ?? 0.0),
            audience: (float) ($data['audience'] ?? 0.0),
            quality: (float) ($data['quality'] ?? 0.0),
            competition: (float) ($data['competition'] ?? 0.0),
            overall: (float) ($data['overall'] ?? 0.0),
            confidence: (float) ($data['confidence'] ?? 0.0),
            generatedAt: $data['generated_at'] ?? null
        );
    }

    /**
     * Cast scores VO back to JSON.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof self) {
            return json_encode([
                'traffic' => $value->traffic,
                'visibility' => $value->visibility,
                'audience' => $value->audience,
                'quality' => $value->quality,
                'competition' => $value->competition,
                'overall' => $value->overall,
                'confidence' => $value->confidence,
                'generated_at' => $value->generatedAt,
            ]);
        }

        if (is_array($value)) {
            return json_encode([
                'traffic' => (float) ($value['traffic'] ?? 0.0),
                'visibility' => (float) ($value['visibility'] ?? 0.0),
                'audience' => (float) ($value['audience'] ?? 0.0),
                'quality' => (float) ($value['quality'] ?? 0.0),
                'competition' => (float) ($value['competition'] ?? 0.0),
                'overall' => (float) ($value['overall'] ?? 0.0),
                'confidence' => (float) ($value['confidence'] ?? 0.0),
                'generated_at' => $value['generated_at'] ?? null,
            ]);
        }

        return null;
    }
}
