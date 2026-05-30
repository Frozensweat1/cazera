@php
    $chartKey = md5(json_encode([$monthlyLabels, $monthlyRevenue, $monthlyExpenses, $paymentBreakdown, $profitSeries]));
@endphp

<div x-data="financialDashboard({
    monthlyLabels: @js($monthlyLabels),
    monthlyRevenue: @js($monthlyRevenue),
    monthlyExpenses: @js($monthlyExpenses),
    paymentBreakdown: @js(array_values($paymentBreakdown)),
    paymentLabels: @js(array_keys($paymentBreakdown))
})" wire:key="financial-dashboard-{{ $chartKey }}">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <ul class="flex space-x-2 text-sm rtl:space-x-reverse">
                <li><a href="javascript:;" class="text-primary hover:underline">Dashboard</a></li>
                <li class="before:content-['/'] ltr:before:mr-1 rtl:before:ml-1"><span>Financial</span></li>
            </ul>
            <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">Financial Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Revenue, operating costs, margins, and holdings for managerial review.</p>
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
            <div class="rounded-xl bg-primary p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Sales</p>
                <p class="mt-4 text-3xl font-bold">GHS {{ number_format($totalRevenue, 2) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">Collected: GHS {{ number_format($collectedRevenue, 2) }}</p>
            </div>
            <div class="rounded-xl bg-success p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Gross Profit</p>
                <p class="mt-4 text-3xl font-bold">GHS {{ number_format($grossProfit, 2) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">Gross margin {{ number_format($grossMargin, 2) }}%</p>
            </div>
            <div class="rounded-xl bg-secondary p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Net Profit</p>
                <p class="mt-4 text-3xl font-bold">GHS {{ number_format($netProfit, 2) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">Net margin {{ number_format($netMargin, 2) }}%</p>
            </div>
            <div class="rounded-xl bg-dark p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Total Holdings</p>
                <p class="mt-4 text-3xl font-bold">GHS {{ number_format($totalHoldings, 2) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">Inventory + trackable menu stock + net profit.</p>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-6">
            <div class="panel border-l-4 border-rose-500 p-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Expenses</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">GHS {{ number_format($totalExpenses, 2) }}</p>
            </div>
            <div class="panel border-l-4 border-amber-500 p-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Maintenance</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">GHS {{ number_format($maintenanceCost, 2) }}</p>
            </div>
            <div class="panel border-l-4 border-cyan-500 p-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Production Cost</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">GHS {{ number_format($productionCost, 2) }}</p>
            </div>
            <div class="panel border-l-4 border-purple-500 p-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Trackable Item Cost</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">GHS {{ number_format($trackableItemCost, 2) }}</p>
            </div>
            <div class="panel border-l-4 border-emerald-500 p-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Inventory Value</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">GHS {{ number_format($inventoryValue, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Registers: GHS {{ number_format($registerHoldings, 2) }}</p>
            </div>
            <div class="panel border-l-4 border-indigo-500 p-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Trackable Menu Value</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">GHS {{ number_format($trackableMenuItemValue, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Quantity on hand at cost.</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Revenue vs Expenses</h5>
                    <span class="text-sm text-slate-500">Selected period</span>
                </div>
                <div wire:ignore>
                    <div x-ref="revenueExpenseChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>

            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Payment Mix</h5>
                    <span class="text-sm text-slate-500">Cash / Card / Other</span>
                </div>
                <div wire:ignore>
                    <div x-ref="paymentBreakdownChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>
        </div>
    </div>
</div>
