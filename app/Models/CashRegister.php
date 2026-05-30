<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasBranchModuleAccess;

    public const EXPECTED_BALANCE_TRANSACTION_TYPES = [
        'sale',
        'refund',
        'cash_in',
        'opening_balance',
    ];

    protected $fillable = [
        'branch_id',
        'module_id',
        'opened_by',
        'closed_by',
        'name',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'actual_balance',
        'difference',
        'is_open',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'actual_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'is_open' => 'boolean',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
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

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashRegisterTransaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function getCalculatedBalanceAttribute(): float
    {
        return $this->transactions()->sum('amount');
    }

    public static function transactionAffectsExpectedBalance(string $type): bool
    {
        return in_array($type, self::EXPECTED_BALANCE_TRANSACTION_TYPES, true);
    }

    public function addExpectedBalanceForTransaction(string $type, float $amount): void
    {
        if (! self::transactionAffectsExpectedBalance($type)) {
            return;
        }

        $this->increment('expected_balance', $amount);
    }
}
