<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'entry_no',      // ◄── PASTIKAN BARIS INI ADA DI SINI
        'entry_date',
        'entry_type',
        'status',
        'description',
        'reference_type',
        'reference_id',
        'total_debit',
        'total_credit',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at'  => 'datetime',
    ];

    public function lines()     { return $this->hasMany(JournalLine::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function postedBy()  { return $this->belongsTo(User::class, 'posted_by'); }
    public function source()    { return $this->morphTo(); }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft'  => '<span class="badge bg-secondary">Draft</span>',
            'posted' => '<span class="badge bg-success">Posted</span>',
            'void'   => '<span class="badge bg-danger">Void</span>',
            default  => '<span class="badge bg-light text-dark">'.$this->status.'</span>',
        };
    }
}