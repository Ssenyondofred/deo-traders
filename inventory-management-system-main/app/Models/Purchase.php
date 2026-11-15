<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Purchase extends Model
{
    protected $fillable = [
        'purchase_no',
        'purchase_date',
        'supplier_id',
        'payment_method',
        'cash_bank_account_id',
        'status',
        'subtotal',
        'discount',
        'is_taxable',
        'tax_percent',
        'tax_amount',
        'grand_total',
        'due_date',
        'notes',
        'rejection_reason',
        'created_by',
        'approved_by',
        'submitted_at',
        'approved_at',
        'posted_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'is_taxable' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function cashBankAccount(): BelongsTo
    {
        return $this->belongsTo(CashBankAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function apDocument(): MorphOne
    {
        return $this->morphOne(ApDocument::class, 'source');
    }

    public function journalEntry(): MorphOne
    {
        return $this->morphOne(JournalEntry::class, 'reference');
    }

    public function paymentMethodEnum(): ?PaymentMethod
    {
        return PaymentMethod::tryFrom($this->payment_method);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            DocumentStatus::Draft->value => 'Draft',
            DocumentStatus::PendingApproval->value => 'Menunggu Approval',
            DocumentStatus::Posted->value => 'Posted',
            DocumentStatus::Rejected->value => 'Ditolak',
            DocumentStatus::Void->value => 'Void',
            default => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            DocumentStatus::Draft->value => 'secondary',
            DocumentStatus::PendingApproval->value => 'warning',
            DocumentStatus::Posted->value => 'success',
            DocumentStatus::Rejected->value => 'danger',
            default => 'light',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [DocumentStatus::Draft->value, DocumentStatus::Rejected->value], true);
    }
}
