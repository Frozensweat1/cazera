<?php

namespace App\Livewire\Backoffice\Dashboards\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasDashboardFilters
{
    public string $filterBranch = '';
    public string $filterModule = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mountDashboardFilters(int $daysBack = 29): void
    {
        $this->dateFrom = $this->dateFrom ?: now()->subDays($daysBack)->toDateString();
        $this->dateTo = $this->dateTo ?: now()->toDateString();
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }

    protected function dashboardBranchId(): ?int
    {
        if ($this->filterBranch !== '') {
            return (int) $this->filterBranch;
        }

        return auth()->user()?->isSuperAdmin()
            ? null
            : (session('branch_id') ? (int) session('branch_id') : null);
    }

    protected function dashboardModuleId(): ?int
    {
        return $this->filterModule !== '' ? (int) $this->filterModule : null;
    }

    protected function dashboardDateRange(): array
    {
        $from = $this->dateFrom
            ? Carbon::parse($this->dateFrom)->startOfDay()
            : now()->startOfDay();

        $to = $this->dateTo
            ? Carbon::parse($this->dateTo)->endOfDay()
            : now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    protected function applyDashboardScope(Builder $query, ?string $branchColumn = null, ?string $moduleColumn = null): Builder
    {
        $query->accessible();

        if ($branchId = $this->dashboardBranchId()) {
            $query->where($branchColumn ?: $query->getModel()->qualifyColumn('branch_id'), $branchId);
        }

        if ($moduleId = $this->dashboardModuleId()) {
            $query->where($moduleColumn ?: $query->getModel()->qualifyColumn('module_id'), $moduleId);
        }

        return $query;
    }

    protected function applyDateRange(Builder $query, string $column): Builder
    {
        [$from, $to] = $this->dashboardDateRange();

        return $query->whereBetween($column, [$from, $to]);
    }
}
