<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class ApDocument extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'document_no', 'source_type', 'source_id',
        'supplier_id', 'document_date', 'due_date',
        'original_amount', 'paid_amount', 'balance_amount',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'due_date'      => 'date',
        'original_amount' => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'balance_amount'  => 'decimal:2',
    ];

    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function payments()  { return $this->hasMany(ApPayment::class); }
    public function source()    { return $this->morphTo(); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open'    => '<span class="badge bg-danger">Belum Lunas</span>',
            'partial' => '<span class="badge bg-warning text-dark">Cicilan</span>',
            'paid'    => '<span class="badge bg-success">Lunas</span>',
            default   => '<span class="badge bg-secondary">'.$this->status.'</span>',
        };
    }
    
    public function statusLabel(): string
    {
        return match ($this->status) {
            'open'    => 'Belum Lunas',
            'partial' => 'Lunas Sebagian',
            'paid'    => 'Lunas',
            default   => ucfirst($this->status),
        };
    }
}