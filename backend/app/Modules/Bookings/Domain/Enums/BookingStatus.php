<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case BranchReview = 'branch_review';
    case ProviderReview = 'provider_review';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Rejected = 'rejected';

    /**
     * Get allowed state transitions.
     *
     * @return array<string, array<int, string>>
     */
    public static function allowedTransitions(): array
    {
        return [
            self::Draft->value => [self::Submitted->value, self::BranchReview->value, self::Cancelled->value],
            self::Submitted->value => [self::BranchReview->value, self::Cancelled->value],
            self::BranchReview->value => [self::ProviderReview->value, self::Rejected->value, self::Cancelled->value],
            self::ProviderReview->value => [self::Approved->value, self::Rejected->value, self::Cancelled->value],
            self::Approved->value => [self::Scheduled->value, self::Cancelled->value],
            self::Scheduled->value => [self::Active->value, self::Cancelled->value],
            self::Active->value => [self::Completed->value, self::Cancelled->value],
            self::Completed->value => [],
            self::Cancelled->value => [],
            self::Expired->value => [],
            self::Rejected->value => [self::Submitted->value], // customer can correct and resubmit
        ];
    }

    public function canTransitionTo(string $target): bool
    {
        return in_array($target, self::allowedTransitions()[$this->value] ?? [], true);
    }
}
