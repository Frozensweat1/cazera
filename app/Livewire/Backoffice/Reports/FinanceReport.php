<?php

namespace App\Livewire\Backoffice\Reports;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\DailyProductionCost;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Sale;
use App\Models\CashRegisterTransaction;
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
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$startDate, $endDate]);

        $expenses = Expense::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $productionCosts = DailyProductionCost::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $maintenance = MaintenanceRequest::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('requested_date', [$startDate, $endDate]);

        $revenue = (clone $sales)->sum('total');
        $collectedRevenue = (clone $sales)->sum('paid_amount');
        $expenseTotal = (clone $expenses)->sum('amount');
        $productionCostTotal = (clone $productionCosts)->sum('amount');
        $maintenanceActualCost = (clone $maintenance)->sum('actual_cost');
        $refunds = abs((float) CashRegisterTransaction::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->where('type', 'refund')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount'));
        $operatingCost = $expenseTotal + $productionCostTotal + $maintenanceActualCost;

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

        $moduleProfitability = (clone $sales)
            ->selectRaw('module_id, sum(total) as gross_revenue, sum(paid_amount) as collected_revenue, count(*) as orders')
            ->with('module')
            ->groupBy('module_id')
            ->orderByDesc('collected_revenue')
            ->take(8)
            ->get()
            ->map(function (Sale $row) use ($branchId, $startDate, $endDate) {
                $production = DailyProductionCost::accessible()
                    ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
                    ->where('module_id', $row->module_id)
                    ->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('amount');
                $expenses = Expense::accessible()
                    ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
                    ->where('module_id', $row->module_id)
                    ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('amount');

                return [
                    'module' => $row->module?->name ?? 'No module',
                    'orders' => $row->orders,
                    'collected' => (float) $row->collected_revenue,
                    'costs' => (float) $production + (float) $expenses,
                    'net' => (float) $row->collected_revenue - (float) $production - (float) $expenses,
                ];
            });

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

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }
}
