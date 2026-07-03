<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class InventoryCapabilities implements CastsAttributes
{
    public function __construct(
        public readonly bool $supportsAudio = false,
        public readonly bool $supportsVideo = false,
        public readonly bool $supportsProgrammatic = false,
        public readonly bool $supportsDayparting = false,
        public readonly bool $supportsLiveContent = false,
        public readonly bool $supportsCreativeRotation = false,
        public readonly bool $supportsProofOfPlay = false
    ) {}

    /**
     * Cast JSON capabilities.
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
            supportsAudio: (bool) ($data['supports_audio'] ?? false),
            supportsVideo: (bool) ($data['supports_video'] ?? false),
            supportsProgrammatic: (bool) ($data['supports_programmatic'] ?? false),
            supportsDayparting: (bool) ($data['supports_dayparting'] ?? false),
            supportsLiveContent: (bool) ($data['supports_live_content'] ?? false),
            supportsCreativeRotation: (bool) ($data['supports_creative_rotation'] ?? false),
            supportsProofOfPlay: (bool) ($data['supports_proof_of_play'] ?? false)
        );
    }

    /**
     * Cast capabilities VO back to JSON.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof self) {
            return json_encode([
                'supports_audio' => $value->supportsAudio,
                'supports_video' => $value->supportsVideo,
                'supports_programmatic' => $value->supportsProgrammatic,
                'supports_dayparting' => $value->supportsDayparting,
                'supports_live_content' => $value->supportsLiveContent,
                'supports_creative_rotation' => $value->supportsCreativeRotation,
                'supports_proof_of_play' => $value->supportsProofOfPlay,
            ]);
        }

        if (is_array($value)) {
            return json_encode([
                'supports_audio' => (bool) ($value['supports_audio'] ?? false),
                'supports_video' => (bool) ($value['supports_video'] ?? false),
                'supports_programmatic' => (bool) ($value['supports_programmatic'] ?? false),
                'supports_dayparting' => (bool) ($value['supports_dayparting'] ?? false),
                'supports_live_content' => (bool) ($value['supports_live_content'] ?? false),
                'supports_creative_rotation' => (bool) ($value['supports_creative_rotation'] ?? false),
                'supports_proof_of_play' => (bool) ($value['supports_proof_of_play'] ?? false),
            ]);
        }

        return null;
    }
}
