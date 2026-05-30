@php
    $chartKey = md5(json_encode([$salesLabels, $salesSeries, $ordersSeries, $categorySeries, $paymentSeries]));
    $statusClasses = [
        'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'served' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        'refunded' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    ];
@endphp

<div x-data="homeDashboard({
    salesLabels: @js($salesLabels),
    salesSeries: @js($salesSeries),
    ordersSeries: @js($ordersSeries),
    categoryLabels: @js($categoryLabels),
    categorySeries: @js($categorySeries),
    paymentLabels: @js($paymentLabels),
    paymentSeries: @js($paymentSeries)
})" wire:key="home-dashboard-{{ $chartKey }}">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <ul class="flex space-x-2 text-sm rtl:space-x-reverse">
                <li><a href="javascript:;" class="text-primary hover:underline">Dashboard</a></li>
                <li class="before:content-['/'] ltr:before:mr-1 rtl:before:ml-1"><span>Live Sales</span></li>
            </ul>
            <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">Live Sales Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Today-first sales pulse with branch and module visibility enforced.</p>
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
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Collected Sales</p>
                <p class="mt-4 text-3xl font-bold">GHS {{ number_format($collectedSales, 2) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">Paid value from completed payments.</p>
            </div>
            <div class="rounded-xl bg-success p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Gross Sales</p>
                <p class="mt-4 text-3xl font-bold">GHS {{ number_format($grossSales, 2) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">{{ number_format($totalOrders) }} orders captured.</p>
            </div>
            <div class="rounded-xl bg-warning p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Outstanding Debt</p>
                <p class="mt-4 text-3xl font-bold">GHS {{ number_format($outstandingDebt, 2) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">Average ticket GHS {{ number_format($averageTicket, 2) }}.</p>
            </div>
            <div class="rounded-xl bg-danger p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Open / Refunds</p>
                <p class="mt-4 text-3xl font-bold">{{ number_format($openOrders) }} / {{ number_format($refundCount) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">{{ number_format($completedOrders) }} completed orders.</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel xl:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h5 class="text-lg font-semibold dark:text-white-light">Sales Trend</h5>
                        <p class="text-sm text-slate-500">Collected sales and order count over the selected period.</p>
                    </div>
                </div>
                <div wire:ignore>
                    <div x-ref="salesTrendChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>

            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Sales By Category</h5>
                    <span class="text-sm text-slate-500">Top 6</span>
                </div>
                <div wire:ignore>
                    <div x-ref="categoryChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Payment Mix</h5>
                    <span class="text-sm text-slate-500">Collected</span>
                </div>
                <div wire:ignore>
                    <div x-ref="paymentMixChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>

            <div class="panel">
                <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Top Menu Items</h5>
                <div class="space-y-3">
                    @forelse ($topItems as $item)
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white-light">{{ $item['name'] }}</p>
                                    <p class="text-sm text-slate-500">GHS {{ number_format($item['revenue'], 2) }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ number_format($item['quantity'], 0) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500 dark:border-slate-700">No item sales in this period.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Recent Sales</h5>
                <div class="space-y-3">
                    @forelse ($recentSales as $sale)
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 dark:text-white-light">{{ $sale['sale_number'] }}</p>
                                    <p class="truncate text-sm text-slate-500">{{ $sale['customer'] }} - {{ $sale['branch'] }} / {{ $sale['module'] }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $sale['time'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-slate-900 dark:text-white-light">GHS {{ number_format($sale['total'], 2) }}</p>
                                    <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$sale['status']] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">
                                        {{ str($sale['status'])->headline() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500 dark:border-slate-700">No recent sales found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
