<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostingItem extends Model
{
    protected $fillable = [
        'costing_sheet_id', 'type', 'description', 'unit', 'quantity', 'unit_cost', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_cost' => 'float',
        'sort_order' => 'integer',
    ];

    protected $appends = ['total'];

    public function costingSheet(): BelongsTo
    {
        return $this->belongsTo(CostingSheet::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }
}
