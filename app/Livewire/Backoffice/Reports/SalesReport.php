<?php

namespace App\Livewire\Backoffice\Reports;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\CashRegisterTransaction;
use App\Models\Module;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\SaleModuleAllocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Component;

class SalesReport extends Component
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
            ->forModule($this->filterModule)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereBetween('sale_date', [$startDate, $endDate]);

        $saleItems = SaleItem::accessible()
            ->when($branchId, fn($query) => $query->where('sale_items.branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('sale_items.module_id', $this->filterModule))
            ->whereHas('sale', fn($query) => $query->whereBetween('sale_date', [$startDate, $endDate])->whereNotIn('status', ['cancelled', 'refunded']));

        $totalOrders = (clone $sales)->count();
        $totalRevenue = SaleModuleAllocation::sum($sales, 'total', $this->filterModule);
        $collectedRevenue = SaleModuleAllocation::sum($sales, 'paid_amount', $this->filterModule);
        $averageOrderValue = $totalOrders ? $totalRevenue / $totalOrders : 0;
        $debtBalance = SaleModuleAllocation::sum((clone $sales)->where('is_debt', true), 'remaining_balance', $this->filterModule);
        $refunds = abs((float) CashRegisterTransaction::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->where('type', 'refund')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount'));
        $collectionRate = $totalRevenue > 0 ? round(($collectedRevenue / $totalRevenue) * 100, 2) : 0;

        $topMenuItems = (clone $saleItems)
            ->selectRaw('menu_item_id, item_name, sum(qty) as total_qty, sum(total) as total_revenue')
            ->groupBy('menu_item_id', 'item_name')
            ->orderByDesc('total_revenue')
            ->take(6)
            ->get();

        $topCategories = (clone $saleItems)
            ->join('menu_items', 'sale_items.menu_item_id', '=', 'menu_items.id')
            ->join('categories', 'menu_items.category_id', '=', 'categories.id')
            ->selectRaw('categories.id as category_id, categories.name as category_name, sum(sale_items.qty) as total_qty, sum(sale_items.total) as total_revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->take(6)
            ->get();

        $topCustomers = (clone $sales)
            ->selectRaw('customer_id, count(*) as orders, sum(total) as spent')
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('spent')
            ->take(6)
            ->get();

        $topStaff = (clone $sales)
            ->selectRaw('created_by, count(*) as orders, sum(total) as revenue')
            ->with('creator')
            ->groupBy('created_by')
            ->orderByDesc('revenue')
            ->take(6)
            ->get();

        $statusBreakdown = (clone $sales)
            ->selectRaw('status, count(*) as orders, sum(total) as total_amount, sum(paid_amount) as paid_amount')
            ->groupBy('status')
            ->orderByDesc('total_amount')
            ->get();

        $moduleBreakdown = $this->moduleSalesBreakdown($branchId, $startDate, $endDate)->take(8);

        return view('livewire.backoffice.reports.sales', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'branchId' => $branchId,
            'filterBranch' => $this->filterBranch,
            'filterModule' => $this->filterModule,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'collectedRevenue' => $collectedRevenue,
            'averageOrderValue' => $averageOrderValue,
            'debtBalance' => $debtBalance,
            'refunds' => $refunds,
            'collectionRate' => $collectionRate,
            'topMenuItems' => $topMenuItems,
            'topCategories' => $topCategories,
            'topCustomers' => $topCustomers,
            'topStaff' => $topStaff,
            'statusBreakdown' => $statusBreakdown,
            'moduleBreakdown' => $moduleBreakdown,
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }

    protected function moduleSalesBreakdown($branchId, Carbon $startDate, Carbon $endDate)
    {
        $sales = Sale::accessible()
            ->with('items')
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->forModule($this->filterModule)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        $modules = Module::query()->whereIn('id', $sales->flatMap->items->pluck('module_id')->filter()->unique())->get()->keyBy('id');

        return $sales
            ->flatMap(function (Sale $sale) {
                $totalSplit = $sale->moduleBreakdownForAmount((float) $sale->total, $sale->items);
                $paidSplit = $sale->moduleBreakdownForAmount((float) $sale->paid_amount, $sale->items);

                return $totalSplit->map(fn ($total, $moduleId) => (object) [
                    'module_id' => (int) $moduleId,
                    'orders' => 1,
                    'total_amount' => (float) $total,
                    'paid_amount' => (float) ($paidSplit->get($moduleId) ?? 0),
                ]);
            })
            ->groupBy('module_id')
            ->map(function ($rows, $moduleId) use ($modules) {
                return (object) [
                    'module' => $modules->get((int) $moduleId),
                    'orders' => $rows->sum('orders'),
                    'total_amount' => $rows->sum('total_amount'),
                    'paid_amount' => $rows->sum('paid_amount'),
                ];
            })
            ->sortByDesc('paid_amount')
            ->values();
    }
}
