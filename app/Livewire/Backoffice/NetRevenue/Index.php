<?php

namespace App\Livewire\Backoffice\NetRevenue;

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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
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

        $salesQuery = Sale::accessible()
            ->with('branch')
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->forModule($this->filterModule)
            ->whereBetween('sale_date', [$startDate, $endDate]);

        $productionQuery = DailyProductionCost::accessible()
            ->with(['branch', 'module'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $expenseQuery = Expense::accessible()
            ->with(['branch', 'module', 'category'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $maintenanceQuery = MaintenanceRequest::accessible()
            ->with(['branch', 'module'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('requested_date', [$startDate, $endDate]);

        $refunds = CashRegisterTransaction::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->where('type', 'refund')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $salesCollected = SaleModuleAllocation::sum($salesQuery, 'paid_amount', $this->filterModule);
        $grossSales = SaleModuleAllocation::sum($salesQuery, 'total', $this->filterModule);
        $productionCosts = (float) (clone $productionQuery)->sum('amount');
        $expenses = (float) (clone $expenseQuery)->sum('amount');
        $maintenanceCosts = (float) (clone $maintenanceQuery)->sum(DB::raw('COALESCE(actual_cost, estimated_cost, 0)'));
        $trackableItemCosts = AccountingMetrics::soldTrackableMenuItemCost(
            SaleItem::accessible()
                ->when($branchId, fn ($query) => $query->where('sale_items.branch_id', $branchId))
                ->when($this->filterModule, fn ($query) => $query->where('sale_items.module_id', $this->filterModule)),
            $startDate,
            $endDate
        );
        $netRevenue = $salesCollected - $trackableItemCosts - $productionCosts - $maintenanceCosts - $expenses;

        $productionByBranchModule = (clone $productionQuery)
            ->selectRaw('branch_id, module_id, sum(amount) as total_amount')
            ->groupBy('branch_id', 'module_id')
            ->get()
            ->keyBy(fn ($row) => $row->branch_id.':'.$row->module_id);
        $expensesByBranchModule = (clone $expenseQuery)
            ->selectRaw('branch_id, module_id, sum(amount) as total_amount')
            ->groupBy('branch_id', 'module_id')
            ->get()
            ->keyBy(fn ($row) => $row->branch_id.':'.$row->module_id);
        $maintenanceByBranchModule = (clone $maintenanceQuery)
            ->selectRaw('branch_id, module_id, sum(COALESCE(actual_cost, estimated_cost, 0)) as total_amount')
            ->groupBy('branch_id', 'module_id')
            ->get()
            ->keyBy(fn ($row) => $row->branch_id.':'.$row->module_id);
        $itemCostByBranchModule = SaleItem::accessible()
            ->when($branchId, fn ($query) => $query->where('sale_items.branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('sale_items.module_id', $this->filterModule))
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->whereNotIn('sales.status', ['cancelled', 'refunded'])
            ->where('sale_items.is_trackable', true)
            ->selectRaw('sale_items.branch_id, sale_items.module_id, sum(sale_items.qty * COALESCE(sale_items.unit_cost, 0)) as total_amount')
            ->groupBy('sale_items.branch_id', 'sale_items.module_id')
            ->get()
            ->keyBy(fn ($row) => $row->branch_id.':'.$row->module_id);

        $salesForBreakdown = (clone $salesQuery)->with(['branch', 'items'])->get();
        $modules = Module::query()
            ->whereIn('id', $salesForBreakdown->flatMap->items->pluck('module_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $branchBreakdown = $salesForBreakdown
            ->flatMap(function (Sale $sale) {
                return $sale->moduleBreakdownForAmount((float) $sale->paid_amount, $sale->items)
                    ->map(fn ($paid, $moduleId) => (object) [
                        'branch_id' => $sale->branch_id,
                        'branch' => $sale->branch,
                        'module_id' => (int) $moduleId,
                        'paid_total' => (float) $paid,
                    ]);
            })
            ->groupBy(fn ($row) => $row->branch_id.':'.$row->module_id)
            ->map(function ($rows) use ($modules, $productionByBranchModule, $expensesByBranchModule, $maintenanceByBranchModule, $itemCostByBranchModule) {
                $row = $rows->first();
                $key = $row->branch_id.':'.$row->module_id;
                $itemCost = (float) ($itemCostByBranchModule->get($key)->total_amount ?? 0);
                $production = (float) ($productionByBranchModule->get($key)->total_amount ?? 0);
                $maintenance = (float) ($maintenanceByBranchModule->get($key)->total_amount ?? 0);
                $expense = (float) ($expensesByBranchModule->get($key)->total_amount ?? 0);
                $paid = (float) $rows->sum('paid_total');

                return [
                    'branch' => $row->branch?->name ?? 'Unknown',
                    'module' => $modules->get($row->module_id)?->name ?? 'No module',
                    'paid' => $paid,
                    'item_cost' => $itemCost,
                    'production' => $production,
                    'maintenance' => $maintenance,
                    'expenses' => $expense,
                    'net' => $paid - $itemCost - $production - $maintenance - $expense,
                ];
            })
            ->sortByDesc('net')
            ->values();

        return view('livewire.backoffice.net-revenue.index', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'summary' => [
                'gross_sales' => $grossSales,
                'sales_collected' => $salesCollected,
                'refunds' => abs((float) $refunds),
                'trackable_item_costs' => $trackableItemCosts,
                'production_costs' => $productionCosts,
                'maintenance_costs' => $maintenanceCosts,
                'expenses' => $expenses,
                'net_revenue' => $netRevenue,
                'margin' => $salesCollected > 0 ? round(($netRevenue / $salesCollected) * 100, 2) : 0,
            ],
            'branchBreakdown' => $branchBreakdown,
            'recentExpenses' => (clone $expenseQuery)->latest('expense_date')->take(6)->get(),
            'recentProductionCosts' => (clone $productionQuery)->latest('production_date')->take(6)->get(),
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }
}
