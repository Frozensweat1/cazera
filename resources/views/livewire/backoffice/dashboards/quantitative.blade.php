@php
    $chartKey = md5(json_encode([$weeklyLabels, $weeklySales, $weeklyOrders, $topSellingItems]));
@endphp

<div x-data="quantitativeDashboard({
    weeklyLabels: @js($weeklyLabels),
    weeklySales: @js($weeklySales),
    weeklyOrders: @js($weeklyOrders),
    topSellingItems: @js($topSellingItems)
})" wire:key="quantitative-dashboard-{{ $chartKey }}">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <ul class="flex space-x-2 text-sm rtl:space-x-reverse">
                <li><a href="javascript:;" class="text-primary hover:underline">Dashboard</a></li>
                <li class="before:content-['/'] ltr:before:mr-1 rtl:before:ml-1"><span>Quantitative</span></li>
            </ul>
            <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white-light">Quantitative Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Volume, throughput, item velocity, and stock risk by period.</p>
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
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Total Orders</p>
                <p class="mt-4 text-3xl font-bold">{{ number_format($totalOrders) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">Order volume in range.</p>
            </div>
            <div class="rounded-xl bg-success p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Items Sold</p>
                <p class="mt-4 text-3xl font-bold">{{ number_format($itemsSold, 0) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">{{ number_format($itemsPerOrder, 2) }} items per order.</p>
            </div>
            <div class="rounded-xl bg-secondary p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Average Ticket</p>
                <p class="mt-4 text-3xl font-bold">GHS {{ number_format($averageOrderValue, 2) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">{{ number_format($uniqueItemsSold) }} unique items sold.</p>
            </div>
            <div class="rounded-xl bg-danger p-5 !text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide !text-white opacity-80">Low Stock</p>
                <p class="mt-4 text-3xl font-bold">{{ number_format($lowStockCount) }}</p>
                <p class="mt-2 text-sm !text-white opacity-80">Trackable items below reorder.</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Sales Velocity</h5>
                    <span class="text-sm text-slate-500">Revenue and orders</span>
                </div>
                <div wire:ignore>
                    <div x-ref="weeklySalesChart" class="rounded-lg bg-white dark:bg-black"></div>
                </div>
            </div>

            <div class="panel">
                <div class="mb-5 flex items-center justify-between">
                    <h5 class="text-lg font-semibold dark:text-white-light">Top Selling Items</h5>
                    <span class="text-sm text-slate-500">Quantity ordered</span>
                </div>
                <div class="space-y-4">
                    @forelse ($topSellingItems as $item)
                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white-light">{{ $item['name'] }}</p>
                                    <p class="text-sm text-slate-500">GHS {{ number_format($item['sales'], 2) }} revenue</p>
                                </div>
                                <div class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    {{ number_format($item['quantity'], 0) }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500 dark:border-slate-700">No item velocity data for this period.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
