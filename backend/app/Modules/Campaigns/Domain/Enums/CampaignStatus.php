<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case ArtworkPending = 'artwork_pending';
    case Scheduled = 'scheduled';
    case Running = 'running';
    case Paused = 'paused';
    case Completed = 'completed';
    case Archived = 'archived';

    /**
     * @return array<string, array<int, string>>
     */
    public static function allowedTransitions(): array
    {
        return [
            self::Draft->value => [self::ArtworkPending->value],
            self::ArtworkPending->value => [self::Scheduled->value, self::Draft->value],
            self::Scheduled->value => [self::Running->value, self::Paused->value],
            self::Running->value => [self::Paused->value, self::Completed->value],
            self::Paused->value => [self::Running->value, self::Completed->value],
            self::Completed->value => [self::Archived->value],
            self::Archived->value => [],
        ];
    }

    public function canTransitionTo(string $target): bool
    {
        return in_array($target, self::allowedTransitions()[$this->value] ?? [], true);
    }
}
