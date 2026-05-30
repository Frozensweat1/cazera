<?php

namespace App\Livewire\Backoffice\Reports;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\MaintenanceRequest;
use App\Models\Sale;
use App\Models\SaleItem;
use Livewire\Component;

class Index extends Component
{
    use HasBranchScope;

    public $filterBranch = '';

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));

        $saleQuery = Sale::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId));

        $saleItemQuery = SaleItem::accessible()
            ->when($branchId, fn($query) => $query->where('sale_items.branch_id', $branchId));

        $expenseQuery = Expense::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId));

        $maintenanceQuery = MaintenanceRequest::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId));

        $inventoryQuery = InventoryItem::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId));

        $totalSales = clone $saleQuery;
        $totalSales = $totalSales->count();

        $totalRevenue = clone $saleQuery;
        $totalRevenue = $totalRevenue->sum('total');

        $averageOrderValue = $totalSales ? $totalRevenue / $totalSales : 0;

        $outstandingDebt = clone $saleQuery;
        $outstandingDebt = $outstandingDebt->where('is_debt', true)->sum('remaining_balance');

        $openMaintenanceRequests = clone $maintenanceQuery;
        $openMaintenanceRequests = $openMaintenanceRequests->whereNotIn('status', ['completed', 'rejected', 'cancelled'])->count();

        $cogs = clone $saleItemQuery;
        $cogs = $cogs
            ->join('menu_items', 'sale_items.menu_item_id', '=', 'menu_items.id')
            ->selectRaw('sum(sale_items.qty * menu_items.cost_price) as cogs')
            ->value('cogs') ?: 0;

        $grossProfit = $totalRevenue - $cogs;
        $grossMargin = $totalRevenue ? round(($grossProfit / $totalRevenue) * 100, 2) : 0;

        $totalInventoryItemsQuery = clone $inventoryQuery;
        $totalInventoryItems = $totalInventoryItemsQuery->count();
        $inventoryValueQuery = clone $inventoryQuery;
        $inventoryValue = $inventoryValueQuery
            ->selectRaw('sum(quantity_on_hand * unit_cost) as value')
            ->value('value') ?: 0;

        $lowStockQuery = clone $inventoryQuery;
        $lowStockCount = $lowStockQuery
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        $reorderAlertsQuery = clone $inventoryQuery;
        $reorderAlerts = $reorderAlertsQuery
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderByRaw('(reorder_level - quantity_on_hand) desc')
            ->take(5)
            ->get();

        $topMenuItemsQuery = clone $saleItemQuery;
        $topMenuItems = $topMenuItemsQuery
            ->selectRaw('menu_item_id, item_name, sum(qty) as total_qty, sum(total) as total_revenue')
            ->groupBy('menu_item_id', 'item_name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        $topCategoriesQuery = clone $saleItemQuery;
        $topCategories = $topCategoriesQuery
            ->join('menu_items', 'sale_items.menu_item_id', '=', 'menu_items.id')
            ->join('categories', 'menu_items.category_id', '=', 'categories.id')
            ->selectRaw('categories.id as category_id, categories.name as category_name, sum(sale_items.qty) as total_qty, sum(sale_items.total) as total_revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        $topCustomersQuery = clone $saleQuery;
        $topCustomers = $topCustomersQuery
            ->selectRaw('customer_id, count(*) as orders, sum(total) as spent')
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('spent')
            ->take(5)
            ->get();

        $topStaffQuery = clone $saleQuery;
        $topStaff = $topStaffQuery
            ->selectRaw('created_by, count(*) as orders, sum(total) as revenue')
            ->with('creator')
            ->groupBy('created_by')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        $expenseCategoriesQuery = clone $expenseQuery;
        $expenseCategories = $expenseCategoriesQuery
            ->selectRaw('expense_category_id, sum(amount) as total_amount')
            ->with('category')
            ->groupBy('expense_category_id')
            ->orderByDesc('total_amount')
            ->take(5)
            ->get();

        $topSuppliersQuery = clone $inventoryQuery;
        $topSuppliers = $topSuppliersQuery
            ->selectRaw('supplier_id, count(*) as item_count, sum(quantity_on_hand * unit_cost) as value_on_hand')
            ->with('supplier')
            ->groupBy('supplier_id')
            ->orderByDesc('value_on_hand')
            ->take(5)
            ->get();

        $maintenanceStatusCountsQuery = clone $maintenanceQuery;
        $maintenanceStatusCounts = $maintenanceStatusCountsQuery
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $completedMaintenanceQuery = clone $maintenanceQuery;
        $completedMaintenance = $completedMaintenanceQuery
            ->whereNotNull('completed_date')
            ->get(['requested_date', 'completed_date']);

        $averageMaintenanceTime = $completedMaintenance->count()
            ? round($completedMaintenance->avg(fn($request) => $request->requested_date?->diffInSeconds($request->completed_date) ?: 0) / 3600, 2)
            : 0;

        $totalMaintenanceRequestsQuery = clone $maintenanceQuery;
        $totalMaintenanceRequests = $totalMaintenanceRequestsQuery->count();

        $maintenanceEstimatedCostQuery = clone $maintenanceQuery;
        $maintenanceEstimatedCost = $maintenanceEstimatedCostQuery->sum('estimated_cost');

        $maintenanceActualCostQuery = clone $maintenanceQuery;
        $maintenanceActualCost = $maintenanceActualCostQuery->sum('actual_cost');

        $totalExpensesQuery = clone $expenseQuery;
        $totalExpenses = $totalExpensesQuery->count();

        $expenseTotalQuery = clone $expenseQuery;
        $expenseTotal = $expenseTotalQuery->sum('amount');

        $recentMaintenanceQuery = clone $maintenanceQuery;
        $recentMaintenance = $recentMaintenanceQuery->latest('requested_date')->take(5)->get();

        $recentExpensesQuery = clone $expenseQuery;
        $recentExpenses = $recentExpensesQuery->latest('expense_date')->take(5)->get();

        return view('livewire.backoffice.reports.index', [
            'branches' => $this->accessibleBranches(),
            'branchId' => $branchId,
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
            'averageOrderValue' => $averageOrderValue,
            'outstandingDebt' => $outstandingDebt,
            'grossMargin' => $grossMargin,
            'totalInventoryItems' => $totalInventoryItems,
            'inventoryValue' => $inventoryValue,
            'lowStockCount' => $lowStockCount,
            'openMaintenanceRequests' => $openMaintenanceRequests,
            'totalMaintenanceRequests' => $totalMaintenanceRequests,
            'maintenanceEstimatedCost' => $maintenanceEstimatedCost,
            'maintenanceActualCost' => $maintenanceActualCost,
            'maintenanceStatusCounts' => $maintenanceStatusCounts,
            'totalExpenses' => $totalExpenses,
            'expenseTotal' => $expenseTotal,
            'topMenuItems' => $topMenuItems,
            'topCategories' => $topCategories,
            'topCustomers' => $topCustomers,
            'topStaff' => $topStaff,
            'expenseCategories' => $expenseCategories,
            'topSuppliers' => $topSuppliers,
            'reorderAlerts' => $reorderAlerts,
            'averageMaintenanceTime' => $averageMaintenanceTime,
            'recentMaintenance' => $recentMaintenance,
            'recentExpenses' => $recentExpenses,
        ]);
    }
}
