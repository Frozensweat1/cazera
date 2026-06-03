<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    use HasBranchModuleAccess;

    protected $fillable = [
        'branch_id',
        'module_id',
        'name',
        'code',
        'type',
        'value',
        'minimum_bill_amount',
        'maximum_discount_amount',
        'description',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'minimum_bill_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function calculateFor(float $billAmount): float
    {
        if ($billAmount < (float) $this->minimum_bill_amount) {
            return 0.0;
        }

        $amount = $this->type === 'percentage'
            ? $billAmount * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->maximum_discount_amount !== null) {
            $amount = min($amount, (float) $this->maximum_discount_amount);
        }

        return round(min($amount, $billAmount), 2);
    }
}
