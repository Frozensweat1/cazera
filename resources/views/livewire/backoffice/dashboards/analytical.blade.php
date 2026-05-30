@php
    $chartKey = md5(json_encode([$periodLabels, $productionCosts, $expenseAmounts, $maintenanceStatus, $moduleRevenue]));
@endphp

<div x-data="analyticalDashboard({
    periodLabels: @js($periodLabels),
    productionCosts: @js($productionCosts),
    expenseLabels: @js($expenseLabels),
    expenseAmounts: @js($expenseAmounts),
    maintenanceStatus: @js($maintenanceStatus),
    moduleLabels: @js($moduleLabels),
    moduleRevenue: @js($moduleRevenue)
})" wire:key="analytical-dashboard-{{ $chartKey }}">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <ul class="flex space-x-2 text-sm rtl:space-x-reverse">
                <li><a href="javascript:;" class="text-primary hover:underline">Dashboard</a></li>
                <li class="before:content-['/'] ltr:before:mr-1 rtl:before:ml-1"><span>Analytical</span></li>
            </ul>
            <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">Analytical Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Decision signals across collections, costs, maintenance, and stock exposure.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <input type="date" wire:model.live="dateFrom" class="form-input" aria-label="From date">
            <input type="date" wire:model.live="dateTo" class="form-input" aria-label="To date">
            <select wire:model.live="filterBranch" class="form-select" aria-label="Branch filter">
                @if (auth()->user()?->isSuperAdmin())
                    <option value="">All branches</option>
                @endif
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterModule" class="form-select" aria-label="Module filter">
                <option value="">All modules</option>
                @foreach ($modules as $module)
                    <option value="{{ $module->id }}">{{ $module->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="pt-5 space-y-6">
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-success p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Collection Rate</p>
                <p class="mt-4 text-3xl font-bold">{{ number_format($collectionRate, 2) }}%</p>
                <p class="mt-2 text-sm !text-white opacity-80">Paid against gross sales.</p>
            </div>
            <div class="rounded-xl bg-warning p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Debt Ratio</p>
                <p class="mt-4 text-3xl font-bold">{{ number_format($debtRatio, 2) }}%</p>
                <p class="mt-2 text-sm !text-white opacity-80">Outstanding against gross sales.</p>
            </div>
            <div class="rounded-xl bg-primary p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Maintenance Completion</p>
                <p class="mt-4 text-3xl font-bold">{{ number_format($maintenanceCompletionRate, 2) }}%</p>
                <p class="mt-2 text-sm !text-white opacity-80">{{ number_format($pendingMaintenance) }} open requests.</p>
            </div>
            <div class="rounded-xl bg-danger p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Stock Risk</p>
                <p class="mt-4 text-3xl font-bold">{{ number_format($stockRiskRate, 2) }}%</p>
                <p class="mt-2 text-sm !text-white opacity-80">{{ number_format($lowStockItems) }} items below reorder.</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Production Cost Trend</h5>
                    <span class="text-sm text-slate-500">Avg GHS {{ number_format($averageProductionCost, 2) }}</span>
                </div>
                <div wire:ignore>
                    <div x-ref="productionCostChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>

            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Expense Category Mix</h5>
                    <span class="text-sm text-slate-500">Top 5</span>
                </div>
                <div wire:ignore>
                    <div x-ref="expenseCategoryChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Maintenance Status</h5>
                    <span class="text-sm text-slate-500">Open vs completed</span>
                </div>
                <div wire:ignore>
                    <div x-ref="maintenanceStatusChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>

            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Module Revenue Contribution</h5>
                    <span class="text-sm text-slate-500">Top modules</span>
                </div>
                <div wire:ignore>
                    <div x-ref="moduleRevenueChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>
        </div>
    </div>
</div>
