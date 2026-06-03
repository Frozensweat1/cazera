<?php

namespace App\Livewire\Backoffice\Reports;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\DailyProductionCost;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ProductionCostsReport extends Component
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

        $productionCosts = DailyProductionCost::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $totalCosts = (clone $productionCosts)->sum('amount');
        $costCount = (clone $productionCosts)->count();
        $lockedEntries = (clone $productionCosts)->where('is_locked', true)->count();
        $pendingCount = max(0, $costCount - $lockedEntries);
        $averageCost = $costCount ? round($totalCosts / $costCount, 2) : 0;
        $dailyAverage = max(1, $startDate->diffInDays($endDate) + 1);
        $dailyAverage = round($totalCosts / $dailyAverage, 2);
        $lastUpdated = (clone $productionCosts)
            ->latest('production_date')
            ->first(['production_date'])
            ?->production_date;

        $recentCostEntries = (clone $productionCosts)
            ->with(['branch', 'module', 'recorder'])
            ->latest('production_date')
            ->take(8)
            ->get();

        $productionByBranch = (clone $productionCosts)
            ->selectRaw('branch_id, sum(amount) as total_amount')
            ->with('branch')
            ->groupBy('branch_id')
            ->orderByDesc('total_amount')
            ->take(6)
            ->get();

        $productionByModule = (clone $productionCosts)
            ->selectRaw('module_id, sum(amount) as total_amount, count(*) as entries')
            ->with('module')
            ->groupBy('module_id')
            ->orderByDesc('total_amount')
            ->take(8)
            ->get();

        $dailyTrend = (clone $productionCosts)
            ->selectRaw('production_date, sum(amount) as total_amount, count(*) as entries')
            ->groupBy('production_date')
            ->orderByDesc('production_date')
            ->take(10)
            ->get();

        return view('livewire.backoffice.reports.production-costs', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'branchId' => $branchId,
            'filterBranch' => $this->filterBranch,
            'filterModule' => $this->filterModule,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'totalProductionCost' => $totalCosts,
            'lockedCount' => $lockedEntries,
            'pendingCount' => $pendingCount,
            'lastUpdated' => $lastUpdated,
            'averageCost' => $averageCost,
            'dailyAverage' => $dailyAverage,
            'recentCostEntries' => $recentCostEntries,
            'productionByBranch' => $productionByBranch,
            'productionByModule' => $productionByModule,
            'dailyTrend' => $dailyTrend,
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }
}
