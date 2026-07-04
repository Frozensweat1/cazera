<?php

namespace App\Support;

use App\Models\DiningTable;
use App\Models\Sale;

class SaleTableRelease
{
    public static function canRelease(Sale $sale): bool
    {
        if (! $sale->table_id) {
            return false;
        }

        $settled = (float) $sale->remaining_balance <= 0
            || in_array($sale->status, ['completed', 'cancelled', 'refunded'], true);

        return $settled && ! static::hasAnotherActiveOrder($sale);
    }

    public static function shouldShowButton(Sale $sale): bool
    {
        return (bool) $sale->table
            && $sale->table->status !== 'available'
            && static::canRelease($sale);
    }

    public static function releaseIfSettled(Sale $sale): bool
    {
        if (! static::canRelease($sale)) {
            return false;
        }

        return static::release($sale);
    }

    public static function release(Sale $sale): bool
    {
        if (! $sale->table_id) {
            return false;
        }

        return (bool) DiningTable::query()
            ->whereKey($sale->table_id)
            ->where('branch_id', $sale->branch_id)
            ->where('status', '!=', 'available')
            ->update(['status' => 'available']);
    }

    protected static function hasAnotherActiveOrder(Sale $sale): bool
    {
        if (! $sale->exists) {
            return false;
        }

        return Sale::query()
            ->where('branch_id', $sale->branch_id)
            ->where('table_id', $sale->table_id)
            ->where($sale->getKeyName(), '!=', $sale->getKey())
            ->where(function ($query) {
                $query->where('remaining_balance', '>', 0)
                    ->orWhereNotIn('status', ['completed', 'cancelled', 'refunded']);
            })
            ->exists();
    }
}
