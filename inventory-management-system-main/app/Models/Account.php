<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'account_type',
        'normal_balance',
        'accurate_type',
        'is_header',
        'is_postable',
        'is_active',
        'level',
        'sort_order',
        'opening_balance',
    ];

    protected $casts = [
        'is_header' => 'boolean',
        'is_postable' => 'boolean',
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getActualBalanceAttribute(): float
    {
        // 1. Ambil nilai Saldo Awal bawaan dari akun ini jika ada
        $openingBalance = (float) $this->opening_balance;

        // 2. Hitung total mutasi dari baris jurnal yang sudah POSTED
        $query = $this->hasMany(\App\Models\JournalLine::class, 'account_id')
            ->whereHas('journalEntry', function($q) {
                $q->where('status', 'posted');
            });

        $debit = (float) $query->sum('debit');
        $credit = (float) $query->sum('credit');

        // 3. Tentukan mutasi bersih (Debit - Kredit)
        $netMovement = $debit - $credit;

        // 4. Jika tipe normal akun adalah DEBIT (seperti Kas & Bank)
        if ($this->normal_balance === 'debit') {
            return $openingBalance + $netMovement;
        }

        // 5. Jika tipe normal akun adalah KREDIT
        return $openingBalance - $netMovement;
    }
}
