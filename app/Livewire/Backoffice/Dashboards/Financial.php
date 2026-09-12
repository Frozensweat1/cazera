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
use App\Support\AccountingMetrics;
use App\Support\SaleModuleAllocation;
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

        $moduleId = $this->dashboardModuleId();

        $this->totalRevenue = SaleModuleAllocation::sum($salesQuery, 'total', $moduleId);
        $this->collectedRevenue = SaleModuleAllocation::sum($salesQuery, 'paid_amount', $moduleId);
        $this->totalExpenses = (float) (clone $expenseQuery)->sum('amount');
        $this->productionCost = (float) (clone $productionQuery)->sum('amount');
        $this->maintenanceCost = (float) (clone $maintenanceQuery)->sum(DB::raw('COALESCE(actual_cost, estimated_cost, 0)'));

        $this->trackableItemCost = AccountingMetrics::soldTrackableMenuItemCost(
            $this->applyDashboardScope(SaleItem::query(), 'sale_items.branch_id', 'sale_items.module_id'),
            $from,
            $to
        );

        $this->grossProfit = $this->collectedRevenue - $this->trackableItemCost;
        $this->netProfit = $this->grossProfit - $this->totalExpenses - $this->maintenanceCost - $this->productionCost;
        $this->grossMargin = $this->collectedRevenue > 0 ? round(($this->grossProfit / $this->collectedRevenue) * 100, 2) : 0.0;
        $this->netMargin = $this->collectedRevenue > 0 ? round(($this->netProfit / $this->collectedRevenue) * 100, 2) : 0.0;

        $this->inventoryValue = AccountingMetrics::inventoryHoldingsAtSellingPrice(
            $this->applyDashboardScope(InventoryItem::query())
        );

        $this->trackableMenuItemValue = AccountingMetrics::menuHoldingsAtSellingPrice(
            $this->applyDashboardScope(MenuItem::query())
        );

        $this->registerHoldings = (float) $this->applyDashboardScope(CashRegister::query())
            ->sum(DB::raw('CASE WHEN is_open = 1 THEN expected_balance ELSE COALESCE(actual_balance, closing_balance, expected_balance, 0) END'));

        $this->totalHoldings = $this->registerHoldings + $this->inventoryValue + $this->trackableMenuItemValue;

        $revenueByDay = SaleModuleAllocation::byDate($salesQuery, 'paid_amount', $moduleId);

        $expenseByDay = (clone $expenseQuery)
            ->selectRaw('DATE(expense_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        $productionByDay = (clone $productionQuery)
            ->selectRaw('DATE(production_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        $maintenanceByDay = (clone $maintenanceQuery)
            ->selectRaw('DATE(requested_date) as date, SUM(COALESCE(actual_cost, estimated_cost, 0)) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        $itemCostByDay = $this->applyDashboardScope(SaleItem::query(), 'sale_items.branch_id', 'sale_items.module_id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->whereNotIn('sales.status', ['cancelled', 'refunded'])
            ->where('sale_items.is_trackable', true)
            ->selectRaw('DATE(sales.sale_date) as date, SUM(sale_items.qty * COALESCE(sale_items.unit_cost, 0)) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $days = collect(CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()));

        $this->monthlyLabels = $days->map(fn ($day) => $day->format('M d'))->toArray();
        $this->monthlyRevenue = $days->map(fn ($day) => round((float) ($revenueByDay->get($day->toDateString())->total ?? 0), 2))->toArray();
        $this->monthlyExpenses = $days->map(function ($day) use ($expenseByDay, $productionByDay, $maintenanceByDay, $itemCostByDay) {
            $date = $day->toDateString();

            return round(
                (float) ($itemCostByDay->get($date)->total ?? 0)
                + (float) ($expenseByDay->get($date)->total ?? 0)
                + (float) ($productionByDay->get($date)->total ?? 0)
                + (float) ($maintenanceByDay->get($date)->total ?? 0),
                2
            );
        })->toArray();
        $this->profitLabels = $this->monthlyLabels;
        $this->profitSeries = collect($this->monthlyRevenue)
            ->map(fn ($revenue, $index) => round((float) $revenue - (float) ($this->monthlyExpenses[$index] ?? 0), 2))
            ->toArray();

        $paymentQuery = $this->applyDateRange($this->applyDashboardScope(Payment::query()), 'paid_at')
            ->whereIn('status', ['completed', 'refunded']);

        $netPaymentAmount = DB::raw("CASE WHEN status = 'refunded' THEN -amount ELSE amount END");
        $this->cashVolume = (float) (clone $paymentQuery)->whereRaw("LOWER(method) = 'cash'")->sum($netPaymentAmount);
        $this->cardVolume = (float) (clone $paymentQuery)->whereRaw("LOWER(method) = 'card'")->sum($netPaymentAmount);
        $this->otherVolume = (float) (clone $paymentQuery)->whereRaw("LOWER(method) NOT IN ('cash', 'card')")->sum('amount');

        $this->paymentBreakdown = [
            'Cash' => $this->cashVolume,
            'Card' => $this->cardVolume,
            'Other' => $this->otherVolume,
        ];
    }
}
