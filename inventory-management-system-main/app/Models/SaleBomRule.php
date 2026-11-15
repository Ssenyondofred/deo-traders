<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleBomRule extends Model
{
    protected $fillable = [
        'finished_product_id',
        'component_product_id',
        'qty_per_unit',
        'is_active',
    ];

    protected $casts = [
        'qty_per_unit' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    public function finishedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'finished_product_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
