<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'type', 'description', 'unit', 'quantity', 'unit_cost', 'selling_price', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_cost' => 'float',
        'selling_price' => 'float',
        'sort_order' => 'integer',
    ];

    protected $appends = ['total_cost', 'total_selling'];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    public function getTotalSellingAttribute(): float
    {
        return $this->quantity * $this->selling_price;
    }
}
