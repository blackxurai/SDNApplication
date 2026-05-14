<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CostingSheet extends Model
{
    protected $fillable = [
        'reference', 'project_name', 'client_name', 'client_email', 'client_phone',
        'prepared_by', 'date', 'notes', 'overhead_percent', 'profit_margin_percent',
        'discount_percent', 'tax_percent', 'status',
    ];

    protected $casts = [
        'date' => 'date',
        'overhead_percent' => 'float',
        'profit_margin_percent' => 'float',
        'discount_percent' => 'float',
        'tax_percent' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CostingItem::class)->orderBy('sort_order');
    }

    public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class);
    }

    public function getMaterialItemsAttribute()
    {
        return $this->items->where('type', 'material');
    }

    public function getLaborItemsAttribute()
    {
        return $this->items->where('type', 'labor');
    }

    public function getRawCostAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_cost);
    }

    public function getOverheadAmountAttribute(): float
    {
        return $this->raw_cost * ($this->overhead_percent / 100);
    }

    public function getCostPlusOverheadAttribute(): float
    {
        return $this->raw_cost + $this->overhead_amount;
    }

    public function getProfitAmountAttribute(): float
    {
        return $this->cost_plus_overhead * ($this->profit_margin_percent / 100);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->cost_plus_overhead + $this->profit_amount;
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
        static::creating(function (self $sheet) {
            if (!$sheet->reference) {
                $sheet->reference = 'CS-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }
}
