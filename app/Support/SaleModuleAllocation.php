<?php

namespace App\Support;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SaleModuleAllocation
{
    public static function sum(Builder $salesQuery, string $amountColumn, mixed $moduleId = null): float
    {
        if (! $moduleId) {
            return (float) (clone $salesQuery)->sum($amountColumn);
        }

        return round((float) (clone $salesQuery)
            ->with('items')
            ->get()
            ->sum(fn (Sale $sale) => $sale->moduleBreakdownForAmount((float) $sale->{$amountColumn}, $sale->items)->get((int) $moduleId, 0)), 2);
    }

    public static function byDate(Builder $salesQuery, string $amountColumn, mixed $moduleId = null): Collection
    {
        if (! $moduleId) {
            return (clone $salesQuery)
                ->selectRaw("DATE(sale_date) as date, SUM({$amountColumn}) as total")
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');
        }

        return (clone $salesQuery)
            ->with('items')
            ->get()
            ->groupBy(fn (Sale $sale) => $sale->sale_date?->toDateString())
            ->map(fn ($sales, $date) => (object) [
                'date' => $date,
                'total' => round((float) $sales->sum(fn (Sale $sale) => $sale->moduleBreakdownForAmount((float) $sale->{$amountColumn}, $sale->items)->get((int) $moduleId, 0)), 2),
            ]);
    }
}
