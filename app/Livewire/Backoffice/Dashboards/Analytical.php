<?php

namespace App\Livewire\Backoffice\Dashboards;

use App\Livewire\Backoffice\Dashboards\Concerns\HasDashboardFilters;
use App\Livewire\Concerns\HasBranchScope;
use App\Models\DailyProductionCost;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\MaintenanceRequest;
use App\Models\Sale;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Analytical extends Component
{
    use HasBranchScope;
    use HasDashboardFilters;

    public array $periodLabels = [];
    public array $productionCosts = [];
    public array $expenseLabels = [];
    public array $expenseAmounts = [];
    public array $maintenanceStatus = [];
    public array $moduleLabels = [];
    public array $moduleRevenue = [];
    public int $lowStockItems = 0;
    public int $pendingMaintenance = 0;
    public float $averageProductionCost = 0.0;
    public float $collectionRate = 0.0;
    public float $debtRatio = 0.0;
    public float $maintenanceCompletionRate = 0.0;
    public float $stockRiskRate = 0.0;

    public function mount(): void
    {
        $this->mountDashboardFilters();
    }

    public function render()
    {
        $this->loadMetrics();

        return view('livewire.backoffice.dashboards.analytical', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->dashboardBranchId()),
        ]);
    }

    protected function loadMetrics(): void
    {
        [$from, $to] = $this->dashboardDateRange();

        $productionQuery = $this->applyDateRange($this->applyDashboardScope(DailyProductionCost::query()), 'production_date');

        $productionByDay = (clone $productionQuery)
            ->selectRaw('DATE(production_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $days = collect(CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()));

        $this->periodLabels = $days->map(fn ($day) => $day->format('M d'))->toArray();
        $this->productionCosts = $days->map(fn ($day) => round((float) ($productionByDay->get($day->toDateString())->total ?? 0), 2))->toArray();

        $expenseCategories = $this->applyDateRange($this->applyDashboardScope(Expense::query()), 'expense_date')
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $this->expenseLabels = $expenseCategories->map(fn ($item) => optional($item->category)->name ?: 'Uncategorized')->toArray();
        $this->expenseAmounts = $expenseCategories->map(fn ($item) => round((float) $item->total, 2))->toArray();

        $maintenanceQuery = $this->applyDateRange($this->applyDashboardScope(MaintenanceRequest::query()), 'requested_date');

        $statusCounts = (clone $maintenanceQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        $this->maintenanceStatus = $statusCounts;
        $this->lowStockItems = (int) $this->applyDashboardScope(InventoryItem::query())
            ->where('is_trackable', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();
        $totalStockItems = (int) $this->applyDashboardScope(InventoryItem::query())->where('is_trackable', true)->count();
        $this->stockRiskRate = $totalStockItems > 0 ? round(($this->lowStockItems / $totalStockItems) * 100, 2) : 0.0;

        $this->pendingMaintenance = (int) (clone $maintenanceQuery)->whereIn('status', ['requested', 'approved', 'in_progress'])->count();
        $totalMaintenance = (int) (clone $maintenanceQuery)->count();
        $completedMaintenance = (int) (clone $maintenanceQuery)->where('status', 'completed')->count();
        $this->maintenanceCompletionRate = $totalMaintenance > 0 ? round(($completedMaintenance / $totalMaintenance) * 100, 2) : 0.0;
        $this->averageProductionCost = round((float) (clone $productionQuery)->avg('amount'), 2);

        $salesQuery = $this->applyDateRange($this->applyDashboardScope(Sale::query()), 'sale_date')
            ->whereNotIn('status', ['cancelled', 'refunded']);
        $totalSales = (float) (clone $salesQuery)->sum('total');
        $paidSales = (float) (clone $salesQuery)->sum('paid_amount');
        $debtSales = (float) (clone $salesQuery)->sum('remaining_balance');
        $this->collectionRate = $totalSales > 0 ? round(($paidSales / $totalSales) * 100, 2) : 0.0;
        $this->debtRatio = $totalSales > 0 ? round(($debtSales / $totalSales) * 100, 2) : 0.0;

        $moduleData = (clone $salesQuery)
            ->leftJoin('modules', 'sales.module_id', '=', 'modules.id')
            ->selectRaw("COALESCE(modules.name, 'No module') as module_name, SUM(sales.total) as total")
            ->groupBy('module_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $this->moduleLabels = $moduleData->pluck('module_name')->toArray();
        $this->moduleRevenue = $moduleData->map(fn ($item) => round((float) $item->total, 2))->toArray();
    }
}
