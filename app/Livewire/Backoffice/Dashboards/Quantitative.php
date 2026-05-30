<?php

namespace App\Livewire\Backoffice\Dashboards;

use App\Livewire\Backoffice\Dashboards\Concerns\HasDashboardFilters;
use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryItem;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Quantitative extends Component
{
    use HasBranchScope;
    use HasDashboardFilters;

    public array $weeklyLabels = [];
    public array $weeklySales = [];
    public array $weeklyOrders = [];
    public int $totalOrders = 0;
    public float $averageOrderValue = 0.0;
    public float $itemsSold = 0.0;
    public int $lowStockCount = 0;
    public int $uniqueItemsSold = 0;
    public float $itemsPerOrder = 0.0;
    public array $topSellingItems = [];

    public function mount(): void
    {
        $this->mountDashboardFilters();
    }

    public function render()
    {
        $this->loadMetrics();

        return view('livewire.backoffice.dashboards.quantitative', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->dashboardBranchId()),
        ]);
    }

    protected function loadMetrics(): void
    {
        [$from, $to] = $this->dashboardDateRange();

        $salesQuery = $this->applyDateRange($this->applyDashboardScope(Sale::query()), 'sale_date')
            ->whereNotIn('status', ['cancelled', 'refunded']);

        $salesByDate = (clone $salesQuery)
            ->selectRaw('DATE(sale_date) as date, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dates = collect(CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()));

        $this->weeklyLabels = $dates->map(fn ($date) => $date->format('M d'))->toArray();
        $this->weeklySales = $dates->map(fn ($date) => round((float) ($salesByDate->get($date->toDateString())->total ?? 0), 2))->toArray();
        $this->weeklyOrders = $dates->map(fn ($date) => (int) ($salesByDate->get($date->toDateString())->orders ?? 0))->toArray();

        $this->totalOrders = (int) (clone $salesQuery)->count();
        $this->averageOrderValue = round((float) (clone $salesQuery)->avg('total'), 2);

        $saleItemQuery = $this->applyDashboardScope(SaleItem::query(), 'sale_items.branch_id', 'sale_items.module_id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->whereNotIn('sales.status', ['cancelled', 'refunded']);

        $this->itemsSold = (float) (clone $saleItemQuery)->sum('sale_items.qty');
        $this->uniqueItemsSold = (int) (clone $saleItemQuery)->distinct('sale_items.menu_item_id')->count('sale_items.menu_item_id');
        $this->itemsPerOrder = $this->totalOrders > 0 ? round($this->itemsSold / $this->totalOrders, 2) : 0.0;

        $this->lowStockCount = (int) $this->applyDashboardScope(InventoryItem::query())
            ->where('is_trackable', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        $this->topSellingItems = (clone $saleItemQuery)
            ->select('sale_items.item_name', DB::raw('SUM(sale_items.qty) as quantity'), DB::raw('SUM(sale_items.total) as sales'))
            ->groupBy('sale_items.item_name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->item_name,
                'quantity' => (float) $item->quantity,
                'sales' => (float) $item->sales,
            ])
            ->toArray();
    }
}
