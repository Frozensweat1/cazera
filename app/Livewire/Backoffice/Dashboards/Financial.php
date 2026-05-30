<?php

namespace App\Livewire\Backoffice\Dashboards;

use App\Livewire\Backoffice\Dashboards\Concerns\HasDashboardFilters;
use App\Livewire\Concerns\HasBranchScope;
use App\Models\CashRegister;
use App\Models\DailyProductionCost;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\MaintenanceRequest;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Financial extends Component
{
    use HasBranchScope;
    use HasDashboardFilters;

    public array $monthlyLabels = [];
    public array $monthlyRevenue = [];
    public array $monthlyExpenses = [];
    public array $profitLabels = [];
    public array $profitSeries = [];
    public float $totalRevenue = 0.0;
    public float $collectedRevenue = 0.0;
    public float $totalExpenses = 0.0;
    public float $maintenanceCost = 0.0;
    public float $productionCost = 0.0;
    public float $trackableItemCost = 0.0;
    public float $grossProfit = 0.0;
    public float $netProfit = 0.0;
    public float $grossMargin = 0.0;
    public float $netMargin = 0.0;
    public float $inventoryValue = 0.0;
    public float $trackableMenuItemValue = 0.0;
    public float $registerHoldings = 0.0;
    public float $totalHoldings = 0.0;
    public float $cashVolume = 0.0;
    public float $cardVolume = 0.0;
    public float $otherVolume = 0.0;
    public array $paymentBreakdown = [];

    public function mount(): void
    {
        $this->mountDashboardFilters();
    }

    public function render()
    {
        $this->loadMetrics();

        return view('livewire.backoffice.dashboards.financial', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->dashboardBranchId()),
        ]);
    }

    protected function loadMetrics(): void
    {
        [$from, $to] = $this->dashboardDateRange();

        $salesQuery = $this->applyDateRange($this->applyDashboardScope(Sale::query()), 'sale_date')
            ->whereNotIn('status', ['cancelled', 'refunded']);

        $expenseQuery = $this->applyDateRange($this->applyDashboardScope(Expense::query()), 'expense_date');
        $productionQuery = $this->applyDateRange($this->applyDashboardScope(DailyProductionCost::query()), 'production_date');
        $maintenanceQuery = $this->applyDateRange($this->applyDashboardScope(MaintenanceRequest::query()), 'requested_date');

        $this->totalRevenue = (float) (clone $salesQuery)->sum('total');
        $this->collectedRevenue = (float) (clone $salesQuery)->sum('paid_amount');
        $this->totalExpenses = (float) (clone $expenseQuery)->sum('amount');
        $this->productionCost = (float) (clone $productionQuery)->sum('amount');
        $this->maintenanceCost = (float) (clone $maintenanceQuery)->sum(DB::raw('COALESCE(actual_cost, estimated_cost, 0)'));

        $this->trackableItemCost = (float) $this->applyDashboardScope(SaleItem::query(), 'sale_items.branch_id', 'sale_items.module_id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('menu_items', 'sale_items.menu_item_id', '=', 'menu_items.id')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->whereNotIn('sales.status', ['cancelled', 'refunded'])
            ->where('menu_items.is_trackable', true)
            ->sum(DB::raw('sale_items.qty * COALESCE(menu_items.cost_price, 0)'));

        $this->grossProfit = $this->collectedRevenue - $this->trackableItemCost;
        $this->netProfit = $this->grossProfit - $this->totalExpenses - $this->maintenanceCost - $this->productionCost;
        $this->grossMargin = $this->collectedRevenue > 0 ? round(($this->grossProfit / $this->collectedRevenue) * 100, 2) : 0.0;
        $this->netMargin = $this->collectedRevenue > 0 ? round(($this->netProfit / $this->collectedRevenue) * 100, 2) : 0.0;

        $this->inventoryValue = (float) $this->applyDashboardScope(InventoryItem::query())
            ->where('is_trackable', true)
            ->where('is_active', true)
            ->sum(DB::raw('quantity_on_hand * COALESCE(unit_cost, 0)'));

        $this->trackableMenuItemValue = (float) $this->applyDashboardScope(MenuItem::query())
            ->where('is_trackable', true)
            ->where('status', '!=', 'unavailable')
            ->sum(DB::raw('COALESCE(quantity, 0) * COALESCE(cost_price, 0)'));

        $this->registerHoldings = (float) $this->applyDashboardScope(CashRegister::query())
            ->sum(DB::raw('CASE WHEN is_open = 1 THEN expected_balance ELSE COALESCE(actual_balance, closing_balance, expected_balance, 0) END'));

        $this->totalHoldings = $this->inventoryValue + $this->trackableMenuItemValue + $this->netProfit;

        $revenueByDay = (clone $salesQuery)
            ->selectRaw('DATE(sale_date) as date, SUM(paid_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $expenseByDay = (clone $expenseQuery)
            ->selectRaw('DATE(expense_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $days = collect(CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()));

        $this->monthlyLabels = $days->map(fn ($day) => $day->format('M d'))->toArray();
        $this->monthlyRevenue = $days->map(fn ($day) => round((float) ($revenueByDay->get($day->toDateString())->total ?? 0), 2))->toArray();
        $this->monthlyExpenses = $days->map(fn ($day) => round((float) ($expenseByDay->get($day->toDateString())->total ?? 0), 2))->toArray();
        $this->profitLabels = $this->monthlyLabels;
        $this->profitSeries = collect($this->monthlyRevenue)
            ->map(fn ($revenue, $index) => round((float) $revenue - (float) ($this->monthlyExpenses[$index] ?? 0), 2))
            ->toArray();

        $paymentQuery = $this->applyDateRange($this->applyDashboardScope(Payment::query()), 'paid_at')
            ->where('status', 'completed');

        $this->cashVolume = (float) (clone $paymentQuery)->whereRaw("LOWER(method) = 'cash'")->sum('amount');
        $this->cardVolume = (float) (clone $paymentQuery)->whereRaw("LOWER(method) = 'card'")->sum('amount');
        $this->otherVolume = (float) (clone $paymentQuery)->whereRaw("LOWER(method) NOT IN ('cash', 'card')")->sum('amount');

        $this->paymentBreakdown = [
            'Cash' => $this->cashVolume,
            'Card' => $this->cardVolume,
            'Other' => $this->otherVolume,
        ];
    }
}
