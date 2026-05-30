<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Sales Report</h2>
                <p class="text-sm text-gray-500">Collected revenue, demand patterns, customer value, and team performance.</p>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-ui.input type="date" name="dateFrom" wire:model.live="dateFrom" />
                <x-ui.input type="date" name="dateTo" wire:model.live="dateTo" />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterModule" wire:model.live="filterModule">
                    <option value="">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="panel"><p class="text-sm text-gray-500">Orders</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($totalOrders) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Gross Sales</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($totalRevenue, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Collected</p><p class="mt-2 text-2xl font-extrabold text-emerald-700">{{ number_format($collectedRevenue, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Collection Rate</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($collectionRate, 2) }}%</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Avg Ticket</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($averageOrderValue, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Debt / Refunds</p><p class="mt-2 text-2xl font-extrabold text-amber-700">{{ number_format($debtBalance, 2) }}</p><p class="text-xs text-gray-500">Refunds {{ number_format($refunds, 2) }}</p></div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Module Performance</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-gray-500"><tr><th class="py-2">Module</th><th>Orders</th><th>Gross</th><th>Collected</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($moduleBreakdown as $row)
                                <tr><td class="py-3 font-semibold">{{ $row->module?->name ?? 'No module' }}</td><td>{{ number_format($row->orders) }}</td><td>{{ number_format($row->total_amount, 2) }}</td><td class="font-semibold text-emerald-700">{{ number_format($row->paid_amount, 2) }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-gray-500">No module sales found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Status Mix</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($statusBreakdown as $status)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <div class="flex justify-between gap-4">
                                <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $status->status)) }}</span>
                                <span>{{ number_format($status->orders) }} orders</span>
                            </div>
                            <div class="mt-1 flex justify-between text-sm text-gray-500">
                                <span>Gross {{ number_format($status->total_amount, 2) }}</span>
                                <span>Paid {{ number_format($status->paid_amount, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No status data available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Top Menu Items</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($topMenuItems as $item)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><div><p class="font-semibold">{{ $item->item_name }}</p><p class="text-xs text-gray-500">Qty {{ number_format($item->total_qty, 2) }}</p></div><span class="font-bold">{{ number_format($item->total_revenue, 2) }}</span></div>
                    @empty
                        <p class="text-sm text-gray-500">No menu item sales found.</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Top Categories</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($topCategories as $category)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><div><p class="font-semibold">{{ $category->category_name }}</p><p class="text-xs text-gray-500">Units {{ number_format($category->total_qty, 2) }}</p></div><span class="font-bold">{{ number_format($category->total_revenue, 2) }}</span></div>
                    @empty
                        <p class="text-sm text-gray-500">No category sales found.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Top Customers</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($topCustomers as $customer)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><div><p class="font-semibold">{{ $customer->customer?->name ?? 'Guest' }}</p><p class="text-xs text-gray-500">Orders {{ number_format($customer->orders) }}</p></div><span class="font-bold">{{ number_format($customer->spent, 2) }}</span></div>
                    @empty
                        <p class="text-sm text-gray-500">No customer sales found.</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Staff Performance</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($topStaff as $staff)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><div><p class="font-semibold">{{ $staff->creator?->name ?? 'Unknown' }}</p><p class="text-xs text-gray-500">Orders {{ number_format($staff->orders) }}</p></div><span class="font-bold">{{ number_format($staff->revenue, 2) }}</span></div>
                    @empty
                        <p class="text-sm text-gray-500">No staff data found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
