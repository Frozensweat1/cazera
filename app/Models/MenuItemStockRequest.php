<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemStockRequest extends Model
{
    use HasBranchModuleAccess;

    protected $fillable = [
        'branch_id',
        'module_id',
        'menu_item_id',
        'requested_by',
        'approved_by',
        'reference_no',
        'requested_qty',
        'quantity_before',
        'quantity_after',
        'status',
        'reason',
        'notes',
        'approval_notes',
        'requested_at',
        'approved_at',
    ];

    protected $casts = [
        'requested_qty' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
