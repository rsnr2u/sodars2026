<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Models\User;
use App\Modules\Bookings\Domain\Enums\PaymentMethod;
use App\Modules\Bookings\Domain\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Platform\Search\Domain\Contracts\Searchable;

class Payment extends BaseBusinessModel implements Searchable
{
    protected $table = 'payments';

    protected $fillable = [
        'organization_id',
        'paymentable_id',
        'paymentable_type',
        'payment_method',
        'amount_cents',
        'reference_number',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'amount_cents' => 'integer',
    ];

    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function toSearchDocument(): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'amount_cents' => $this->amount_cents,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'finance_payments';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'reference_number' => 'text',
            'status' => 'keyword',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
