<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApPayment extends Model
{
    protected $fillable = [
        'ap_document_id', 'payment_no', 'payment_date',
        'amount', 'cash_bank_account_id', 'notes',
        'journal_entry_id', 'created_by',
    ];

    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2'];

    public function apDocument()      { return $this->belongsTo(ApDocument::class); }
    public function cashBankAccount() { return $this->belongsTo(CashBankAccount::class); }
    public function journalEntry()    { return $this->belongsTo(JournalEntry::class); }
}