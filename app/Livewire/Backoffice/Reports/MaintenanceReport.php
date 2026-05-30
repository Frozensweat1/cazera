<?php

namespace App\Livewire\Backoffice\Reports;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Carbon;
use Livewire\Component;

class MaintenanceReport extends Component
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

        $maintenance = MaintenanceRequest::accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('requested_date', [$startDate, $endDate]);

        $totalRequests = (clone $maintenance)->count();
        $estimatedCost = (clone $maintenance)->sum('estimated_cost');
        $actualCost = (clone $maintenance)->sum('actual_cost');
        $costVariance = $actualCost - $estimatedCost;
        $openRequests = (clone $maintenance)
            ->whereNotIn('status', ['completed', 'rejected', 'cancelled'])
            ->count();
        $urgentOpenRequests = (clone $maintenance)
            ->where('priority', 'urgent')
            ->whereNotIn('status', ['completed', 'rejected', 'cancelled'])
            ->count();
        $overdueRequests = (clone $maintenance)
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<', now()->toDateString())
            ->whereNotIn('status', ['completed', 'rejected', 'cancelled'])
            ->count();
        $lockedRequests = (clone $maintenance)->where('is_locked', true)->count();

        $statusCounts = (clone $maintenance)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $topModules = (clone $maintenance)
            ->selectRaw('module_id, count(*) as total_requests, sum(actual_cost) as total_cost')
            ->with('module')
            ->groupBy('module_id')
            ->orderByDesc('total_requests')
            ->take(6)
            ->get();

        $recentRequests = (clone $maintenance)
            ->with(['branch', 'module'])
            ->latest('requested_date')
            ->take(6)
            ->get();

        $completedRequests = (clone $maintenance)
            ->whereNotNull('completed_date')
            ->get(['requested_date', 'completed_date']);

        $averageCompletion = $completedRequests->count()
            ? round($completedRequests->avg(fn($request) => $request->requested_date?->diffInSeconds($request->completed_date) ?: 0) / 3600, 2)
            : 0;
        $completionRate = $totalRequests ? round((($statusCounts['completed'] ?? 0) / $totalRequests) * 100, 2) : 0;

        return view('livewire.backoffice.reports.maintenance', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'branchId' => $branchId,
            'totalRequests' => $totalRequests,
            'estimatedCost' => $estimatedCost,
            'actualCost' => $actualCost,
            'costVariance' => $costVariance,
            'openRequests' => $openRequests,
            'urgentOpenRequests' => $urgentOpenRequests,
            'overdueRequests' => $overdueRequests,
            'lockedRequests' => $lockedRequests,
            'statusCounts' => $statusCounts,
            'topModules' => $topModules,
            'recentRequests' => $recentRequests,
            'averageCompletion' => $averageCompletion,
            'completionRate' => $completionRate,
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }
}
