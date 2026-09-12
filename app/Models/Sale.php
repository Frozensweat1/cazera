<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Sale extends Model
{
    use HasBranchModuleAccess;

    protected $fillable = [
        'branch_id',
        'table_id',
        'customer_id',
        'created_by',
        'sale_number',
        'type',
        'status',
        'subtotal',
        'tax',
        'discount',
        'service_charge',
        'total',
        'paid_amount',
        'refunded_amount',
        'remaining_balance',
        'is_debt',
        'sale_date',
        'served_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'is_debt' => 'boolean',
        'sale_date' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'sale_items')->distinct();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany('paid_at');
    }

    public function cashRegisterTransactions(): HasMany
    {
        return $this->hasMany(CashRegisterTransaction::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount - (float) $this->refunded_amount);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->remaining_balance <= 0;
    }

    public function getModuleNamesAttribute(): string
    {
        $modules = $this->relationLoaded('modules') ? $this->modules : $this->modules()->get();

        return $modules->pluck('name')->filter()->unique()->implode(', ') ?: 'No module';
    }

    public function moduleBreakdownForAmount(float $amount, $items): Collection
    {
        $itemTotals = collect($items)
            ->filter(fn ($item) => ! empty($item->module_id))
            ->groupBy(fn ($item) => (int) $item->module_id)
            ->map(fn ($group) => round((float) $group->sum('subtotal'), 2));

        if ($itemTotals->isEmpty()) {
            return collect();
        }

        $total = max(1, round((float) $itemTotals->sum(), 2));
        $shares = $itemTotals->map(fn ($subtotal) => $subtotal / $total);

        $allocated = $shares->map(fn ($share) => round($amount * $share, 2));
        $diff = round($amount - $allocated->sum(), 2);

        if ($diff !== 0.0 && $allocated->isNotEmpty()) {
            $firstKey = $allocated->keys()->first();
            $allocated[$firstKey] = round($allocated[$firstKey] + $diff, 2);
        }

        return $allocated;
    }

    public function scopeForModule($query, mixed $moduleId)
    {
        return $moduleId
            ? $query->whereHas('items', fn ($query) => $query->where('module_id', $moduleId))
            : $query;
    }

    public function scopeForModules($query, $moduleIds)
    {
        $moduleIds = collect($moduleIds)->filter()->values();

        return $moduleIds->isNotEmpty()
            ? $query->whereHas('items', fn ($query) => $query->whereIn('module_id', $moduleIds))
            : $query;
    }
}
