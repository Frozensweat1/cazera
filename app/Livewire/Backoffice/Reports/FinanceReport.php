<?php

namespace App\Livewire\Backoffice\Reports;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\CashRegisterTransaction;
use App\Models\DailyProductionCost;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Module;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\AccountingMetrics;
use App\Support\SaleModuleAllocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Component;

class FinanceReport extends Component
{
    use HasBranchScope;

    public $filterBranch = '';

    public $filterModule = '';

    public $dateFrom = '';

    public $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));
        $startDate = Carbon::parse($this->dateFrom ?: now()->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($this->dateTo ?: now()->toDateString())->endOfDay();

        $sales = Sale::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->forModule($this->filterModule)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereBetween('sale_date', [$startDate, $endDate]);

        $expenses = Expense::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $productionCosts = DailyProductionCost::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $maintenance = MaintenanceRequest::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('requested_date', [$startDate, $endDate]);

        $revenue = SaleModuleAllocation::sum($sales, 'total', $this->filterModule);
        $collectedRevenue = SaleModuleAllocation::sum($sales, 'paid_amount', $this->filterModule);
        $expenseTotal = (clone $expenses)->sum('amount');
        $productionCostTotal = (clone $productionCosts)->sum('amount');
        $maintenanceActualCost = (clone $maintenance)->sum('actual_cost');
        $trackableItemCost = AccountingMetrics::soldTrackableMenuItemCost(
            SaleItem::accessible()
                ->when($branchId, fn ($query) => $query->where('sale_items.branch_id', $branchId))
                ->when($this->filterModule, fn ($query) => $query->where('sale_items.module_id', $this->filterModule)),
            $startDate,
            $endDate
        );
        $refunds = abs((float) CashRegisterTransaction::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->where('type', 'refund')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount'));
        $operatingCost = $trackableItemCost + $expenseTotal + $productionCostTotal + $maintenanceActualCost;

        $profitEstimate = $collectedRevenue - $operatingCost;
        $profitMargin = $collectedRevenue ? round(($profitEstimate / $collectedRevenue) * 100, 2) : 0;

        $expenseCategories = (clone $expenses)
            ->selectRaw('expense_category_id, sum(amount) as total_amount')
            ->with('category')
            ->groupBy('expense_category_id')
            ->orderByDesc('total_amount')
            ->take(6)
            ->get();

        $productionByBranch = (clone $productionCosts)
            ->selectRaw('branch_id, sum(amount) as total_amount')
            ->with('branch')
            ->groupBy('branch_id')
            ->orderByDesc('total_amount')
            ->take(6)
            ->get();

        $moduleProfitability = $this->moduleProfitability($branchId, $startDate, $endDate)->take(8);

        return view('livewire.backoffice.reports.finance', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'branchId' => $branchId,
            'filterBranch' => $this->filterBranch,
            'filterModule' => $this->filterModule,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'revenue' => $revenue,
            'collectedRevenue' => $collectedRevenue,
            'refunds' => $refunds,
            'trackableItemCost' => $trackableItemCost,
            'expenseTotal' => $expenseTotal,
            'productionCostTotal' => $productionCostTotal,
            'maintenanceActualCost' => $maintenanceActualCost,
            'operatingCost' => $operatingCost,
            'profitEstimate' => $profitEstimate,
            'profitMargin' => $profitMargin,
            'expenseCategories' => $expenseCategories,
            'productionByBranch' => $productionByBranch,
            'moduleProfitability' => $moduleProfitability,
        ]);
    }

    protected function moduleProfitability($branchId, Carbon $startDate, Carbon $endDate)
    {
        $sales = Sale::accessible()
            ->with('items')
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->forModule($this->filterModule)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        $modules = Module::query()->whereIn('id', $sales->flatMap->items->pluck('module_id')->filter()->unique())->get()->keyBy('id');
        $productionCosts = DailyProductionCost::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('module_id, sum(amount) as total_amount')
            ->groupBy('module_id')
            ->pluck('total_amount', 'module_id');
        $expenseCosts = Expense::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('module_id, sum(amount) as total_amount')
            ->groupBy('module_id')
            ->pluck('total_amount', 'module_id');
        $maintenanceCosts = MaintenanceRequest::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('requested_date', [$startDate, $endDate])
            ->selectRaw('module_id, sum(actual_cost) as total_amount')
            ->groupBy('module_id')
            ->pluck('total_amount', 'module_id');
        $trackableItemCosts = SaleItem::accessible()
            ->when($branchId, fn ($query) => $query->where('sale_items.branch_id', $branchId))
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->whereNotIn('sales.status', ['cancelled', 'refunded'])
            ->where('sale_items.is_trackable', true)
            ->selectRaw('sale_items.module_id, sum(sale_items.qty * COALESCE(sale_items.unit_cost, 0)) as total_amount')
            ->groupBy('sale_items.module_id')
            ->pluck('total_amount', 'sale_items.module_id');

        return $sales
            ->flatMap(function (Sale $sale) {
                return $sale->moduleBreakdownForAmount((float) $sale->paid_amount, $sale->items)
                    ->map(fn ($collected, $moduleId) => (object) [
                        'module_id' => (int) $moduleId,
                        'orders' => 1,
                        'collected_revenue' => (float) $collected,
                    ]);
            })
            ->groupBy('module_id')
            ->map(function ($rows, $moduleId) use ($modules, $productionCosts, $expenseCosts, $maintenanceCosts, $trackableItemCosts) {
                $production = (float) ($productionCosts->get($moduleId) ?? 0);
                $expenses = (float) ($expenseCosts->get($moduleId) ?? 0);
                $maintenance = (float) ($maintenanceCosts->get($moduleId) ?? 0);
                $itemCost = (float) ($trackableItemCosts->get($moduleId) ?? 0);
                $collected = (float) $rows->sum('collected_revenue');
                $costs = $itemCost + $production + $expenses + $maintenance;

                return [
                    'module' => $modules->get((int) $moduleId)?->name ?? 'No module',
                    'orders' => $rows->sum('orders'),
                    'collected' => $collected,
                    'costs' => $costs,
                    'net' => $collected - $costs,
                ];
            })
            ->sortByDesc('collected')
            ->values();
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }
}
