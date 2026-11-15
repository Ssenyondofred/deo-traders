<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'qty_on_hand',
        'avg_cost',
    ];

    protected $casts = [
        'qty_on_hand' => 'decimal:4',
        'avg_cost' => 'decimal:4',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockValue(): float
    {
        return (float) $this->qty_on_hand * (float) $this->avg_cost;
    }
}
