<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryPurchaseOrderRequest extends Model
{
    use HasBranchModuleAccess;

    protected $fillable = [
        'branch_id',
        'module_id',
        'inventory_item_id',
        'supplier_id',
        'requested_by',
        'approved_by',
        'reference_no',
        'requested_qty',
        'unit_cost',
        'total_cost',
        'quantity_before',
        'quantity_after',
        'status',
        'reason',
        'notes',
        'approval_notes',
        'expected_delivery_date',
        'requested_at',
        'approved_at',
    ];

    protected $casts = [
        'requested_qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
        'expected_delivery_date' => 'date',
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

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
