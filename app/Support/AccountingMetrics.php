<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AccountingMetrics
{
    public static function soldTrackableMenuItemCost(Builder $saleItemsQuery, CarbonInterface|string|null $from = null, CarbonInterface|string|null $to = null): float
    {
        $query = (clone $saleItemsQuery)
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('menu_items', 'sale_items.menu_item_id', '=', 'menu_items.id')
            ->whereNotIn('sales.status', ['cancelled', 'refunded'])
            ->where('menu_items.is_trackable', true);

        if ($from && $to) {
            $query->whereBetween('sales.sale_date', [$from, $to]);
        }

        return (float) $query->sum(DB::raw('sale_items.qty * COALESCE(menu_items.cost_price, 0)'));
    }

    public static function inventoryHoldingsAtSellingPrice(Builder $inventoryQuery): float
    {
        return (float) (clone $inventoryQuery)
            ->where('is_trackable', true)
            ->where('is_active', true)
            ->sum(DB::raw('quantity_on_hand * COALESCE(unit_price, 0)'));
    }

    public static function menuHoldingsAtSellingPrice(Builder $menuItemQuery): float
    {
        return (float) (clone $menuItemQuery)
            ->where('is_trackable', true)
            ->where('status', '!=', 'unavailable')
            ->sum(DB::raw('COALESCE(quantity, 0) * COALESCE(price, 0)'));
    }
}
