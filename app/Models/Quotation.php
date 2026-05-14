<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $fillable = [
        'reference', 'costing_sheet_id', 'project_name', 'client_name', 'client_email',
        'client_phone', 'prepared_by', 'date', 'valid_until', 'notes', 'terms',
        'overhead_percent', 'profit_margin_percent', 'discount_percent', 'tax_percent', 'status',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
        'overhead_percent' => 'float',
        'profit_margin_percent' => 'float',
        'discount_percent' => 'float',
        'tax_percent' => 'float',
    ];

    public function costingSheet(): BelongsTo
    {
        return $this->belongsTo(CostingSheet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function getRawCostAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_cost);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->selling_price);
    }

    public function getDiscountAmountAttribute(): float
    {
        return $this->subtotal * ($this->discount_percent / 100);
    }

    public function getTaxAmountAttribute(): float
    {
        return ($this->subtotal - $this->discount_amount) * ($this->tax_percent / 100);
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal - $this->discount_amount + $this->tax_amount;
    }

    protected static function booted(): void
    {
        static::creating(function (self $q) {
            if (!$q->reference) {
                $q->reference = 'QT-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }
}
