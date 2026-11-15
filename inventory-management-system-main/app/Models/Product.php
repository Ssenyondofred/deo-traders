<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'default_price',
        'avg_cost',
        'track_stock',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'avg_cost' => 'decimal:4',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function stockBalance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StockBalance::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'raw_material' => 'Bahan Baku',
            'finished_goods' => 'Barang Jadi',
            'fuel' => 'Bahan Bakar',
            'packaging' => 'Kemasan',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }

    public function currentUnitPrice(): float
    {
        $latest = $this->prices()
            ->where('effective_date', '<=', now()->toDateString())
            ->orderByDesc('effective_date')
            ->value('price');

        return (float) ($latest ?? $this->default_price);
    }
}
