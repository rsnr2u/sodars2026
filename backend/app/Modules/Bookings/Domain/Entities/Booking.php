<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Platform\Search\Domain\Contracts\Searchable;

class Booking extends BaseModel implements Searchable
{
    protected $table = 'bookings';

    protected $fillable = [
        'booking_code',
        'customer_id',
        'branch_id',
        'start_date',
        'end_date',
        'subtotal_cents',
        'discount_cents',
        'tax_cents',
        'platform_fee_cents',
        'provider_share_cents',
        'commission_cents',
        'grand_total_cents',
        'currency',
        'status',
        'booking_snapshot',
        'quotation_snapshot',
        'quotation_id',
        'quotation_version_id',
        'converted_from_quotation_at',
    ];

    protected $casts = [
        'status' => BookingStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'subtotal_cents' => 'integer',
        'discount_cents' => 'integer',
        'tax_cents' => 'integer',
        'platform_fee_cents' => 'integer',
        'provider_share_cents' => 'integer',
        'commission_cents' => 'integer',
        'grand_total_cents' => 'integer',
        'booking_snapshot' => 'array',
        'quotation_snapshot' => 'array',
        'converted_from_quotation_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class, 'booking_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'booking_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BookingDocument::class, 'booking_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BookingNote::class, 'booking_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(BookingActivity::class, 'booking_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Domain\Entities\Quotation::class, 'quotation_id');
    }

    public function quotationVersion(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Domain\Entities\QuotationVersion::class, 'quotation_version_id');
    }

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->booking_code,
                $this->currency,
            ])),
            'filterable_attributes' => [
                'status' => $this->status instanceof BookingStatus ? $this->status->value : $this->status,
                'customer_id' => $this->customer_id,
                'branch_id' => $this->branch_id,
                'currency' => $this->currency,
            ],
            'facet_values' => [
                'status' => $this->status instanceof BookingStatus ? $this->status->value : $this->status,
                'currency' => $this->currency,
            ],
            'sortable_attributes' => [
                'created_at' => $this->created_at?->toIso8601String(),
                'grand_total_cents' => $this->grand_total_cents,
            ],
            'display_data' => [
                'name' => 'Booking ' . $this->booking_code,
                'code' => $this->booking_code,
                'status' => $this->status instanceof BookingStatus ? $this->status->value : $this->status,
                'grand_total_cents' => $this->grand_total_cents,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'bookings';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'booking_code' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status', 'currency'];
    }
}
