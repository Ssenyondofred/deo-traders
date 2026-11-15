<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ProductionBatch extends Model
{
    protected $fillable = [
        'batch_no',
        'production_date',
        'batch_label',
        'status',
        'total_material_cost',
        'total_output_qty',
        'output_unit_cost',
        'notes',
        'rejection_reason',
        'created_by',
        'approved_by',
        'submitted_at',
        'approved_at',
        'posted_at',
    ];

    protected $casts = [
        'production_date' => 'date',
        'total_material_cost' => 'decimal:2',
        'total_output_qty' => 'decimal:4',
        'output_unit_cost' => 'decimal:4',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ProductionOutput::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function journalEntry(): MorphOne
    {
        return $this->morphOne(JournalEntry::class, 'reference');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            DocumentStatus::Draft->value => 'Draft',
            DocumentStatus::PendingApproval->value => 'Menunggu Approval',
            DocumentStatus::Posted->value => 'Posted',
            DocumentStatus::Rejected->value => 'Ditolak',
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
