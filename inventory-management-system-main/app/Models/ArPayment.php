<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArPayment extends Model
{
    protected $fillable = [
        'ar_document_id', 'payment_no', 'payment_date',
        'amount', 'cash_bank_account_id', 'notes',
        'journal_entry_id', 'created_by',
    ];

    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2'];

    public function arDocument()      { return $this->belongsTo(ArDocument::class); }
    public function cashBankAccount() { return $this->belongsTo(CashBankAccount::class); }
    public function journalEntry()    { return $this->belongsTo(JournalEntry::class); }
}