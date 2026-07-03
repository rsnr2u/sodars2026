<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Finance\Domain\Enums\InvoiceStatus;
use App\Modules\Finance\Domain\Enums\InvoiceType;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Platform\Search\Domain\Contracts\Searchable;

class Invoice extends BaseModel implements Searchable
{
    protected $table = 'invoices';

    protected $fillable = [
        'id',
        'invoice_number',
        'booking_id',
        'customer_id',
        'branch_id',
        'issue_date',
        'due_date',
        'subtotal_cents',
        'discount_cents',
        'tax_cents',
        'grand_total_cents',
        'currency',
        'status',
        'invoice_type',
        'booking_snapshot',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal_cents' => 'integer',
        'discount_cents' => 'integer',
        'tax_cents' => 'integer',
        'grand_total_cents' => 'integer',
        'booking_snapshot' => 'array',
        'status' => InvoiceStatus::class,
        'invoice_type' => InvoiceType::class,
    ];

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
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InvoiceAdjustment::class, 'invoice_id');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(InvoiceTax::class, 'invoice_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(InvoiceActivity::class, 'invoice_id');
    }

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->invoice_number,
                $this->currency,
            ])),
            'filterable_attributes' => [
                'status' => $this->status instanceof InvoiceStatus ? $this->status->value : $this->status,
                'customer_id' => $this->customer_id,
                'branch_id' => $this->branch_id,
                'invoice_type' => $this->invoice_type instanceof InvoiceType ? $this->invoice_type->value : $this->invoice_type,
                'currency' => $this->currency,
            ],
            'facet_values' => [
                'status' => $this->status instanceof InvoiceStatus ? $this->status->value : $this->status,
                'invoice_type' => $this->invoice_type instanceof InvoiceType ? $this->invoice_type->value : $this->invoice_type,
            ],
            'sortable_attributes' => [
                'created_at' => $this->created_at?->toIso8601String(),
                'grand_total_cents' => $this->grand_total_cents,
            ],
            'display_data' => [
                'name' => 'Invoice ' . $this->invoice_number,
                'code' => $this->invoice_number,
                'status' => $this->status instanceof InvoiceStatus ? $this->status->value : $this->status,
                'grand_total_cents' => $this->grand_total_cents,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'invoices';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'invoice_number' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status', 'invoice_type'];
    }
}
