<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

   protected $fillable = [
    'purchase_number',
    'supplier_id',
    'purchase_date',
    'subtotal',
    'discount',
    'tax',
    'total',
    'paid',
    'balance',
    'status',
    'notes',
];
    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}