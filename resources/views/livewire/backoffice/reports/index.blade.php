<div>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Reports</h2>
            <p class="text-sm text-gray-500">Live operational reporting for sales, inventory, finance, maintenance and
                customer performance.</p>
        </div>
        <div class="report-print-hidden flex flex-wrap items-center gap-2">
            <x-ui.select label="Branch" name="filterBranch" wire:model.live="filterBranch">
                <option value="">All Branches</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </x-ui.select>
            <x-reports.pdf-export-button :href="route('backoffice.reports.pdf', [
                'report' => 'overview',
                'filterBranch' => $filterBranch,
            ])" class="w-full sm:w-auto" />
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-4">
        <x-ui.card>
            <div class="text-sm text-gray-500">Total Sales</div>
            <div class="mt-3 text-3xl font-bold">{{ number_format($totalSales) }}</div>
            <div class="mt-2 text-sm text-gray-500">Orders captured for the selected branch.</div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-gray-500">Revenue</div>
            <div class="mt-3 text-3xl font-bold">${{ number_format($totalRevenue, 2) }}</div>
            <div class="mt-2 text-sm text-gray-500">Gross sales revenue in the period.</div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-gray-500">Avg. Order Value</div>
            <div class="mt-3 text-3xl font-bold">${{ number_format($averageOrderValue, 2) }}</div>
            <div class="mt-2 text-sm text-gray-500">Average sale ticket size.</div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-gray-500">Outstanding Debt</div>
            <div class="mt-3 text-3xl font-bold">${{ number_format($outstandingDebt, 2) }}</div>
            <div class="mt-2 text-sm text-gray-500">Current uncollected balance from outstanding sales.</div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-gray-500">Inventory Value</div>
            <div class="mt-3 text-3xl font-bold">${{ number_format($inventoryValue, 2) }}</div>
            <div class="mt-2 text-sm text-gray-500">Estimated value of stock on hand.</div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-gray-500">Low Stock Items</div>
            <div class="mt-3 text-3xl font-bold">{{ number_format($lowStockCount) }}</div>
            <div class="mt-2 text-sm text-gray-500">Items at or below reorder levels.</div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-gray-500">Gross Margin</div>
            <div class="mt-3 text-3xl font-bold">{{ number_format($grossMargin, 2) }}%</div>
            <div class="mt-2 text-sm text-gray-500">Revenue after estimated menu cost of goods sold.</div>
        </x-ui.card>

        <x-ui.card>
            <div class="text-sm text-gray-500">Open Maintenance</div>
            <div class="mt-3 text-3xl font-bold">{{ number_format($openMaintenanceRequests) }}</div>
            <div class="mt-2 text-sm text-gray-500">Requests still waiting for completion or approval.</div>
        </x-ui.card>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('backoffice.reports.sales') }}"
            class="rounded-3xl border border-gray-200 bg-white p-4 text-left transition hover:border-primary hover:bg-primary/5">
            <h3 class="text-lg font-semibold text-gray-900">Sales report</h3>
            <p class="mt-2 text-sm text-gray-500">Drill into menu, customer, and staff performance.</p>
        </a>
        <a href="{{ route('backoffice.reports.inventory') }}"
            class="rounded-3xl border border-gray-200 bg-white p-4 text-left transition hover:border-primary hover:bg-primary/5">
            <h3 class="text-lg font-semibold text-gray-900">Inventory report</h3>
            <p class="mt-2 text-sm text-gray-500">Review stock value, low inventory, and supplier exposure.</p>
        </a>
        <a href="{{ route('backoffice.reports.finance') }}"
            class="rounded-3xl border border-gray-200 bg-white p-4 text-left transition hover:border-primary hover:bg-primary/5">
            <h3 class="text-lg font-semibold text-gray-900">Finance report</h3>
            <p class="mt-2 text-sm text-gray-500">See revenue, expenses, production cost, and profit.</p>
        </a>
        <a href="{{ route('backoffice.reports.maintenance') }}"
            class="rounded-3xl border border-gray-200 bg-white p-4 text-left transition hover:border-primary hover:bg-primary/5">
            <h3 class="text-lg font-semibold text-gray-900">Maintenance report</h3>
            <p class="mt-2 text-sm text-gray-500">Track request backlog, costs, and module impact.</p>
        </a>
        <a href="{{ route('backoffice.reports.production-costs') }}"
            class="rounded-3xl border border-gray-200 bg-white p-4 text-left transition hover:border-primary hover:bg-primary/5">
            <h3 class="text-lg font-semibold text-gray-900">Production cost report</h3>
            <p class="mt-2 text-sm text-gray-500">Monitor daily production expenses and locked entries.</p>
        </a>
        <a href="{{ route('backoffice.reports.cash-register') }}"
            class="rounded-3xl border border-gray-200 bg-white p-4 text-left transition hover:border-primary hover:bg-primary/5">
            <h3 class="text-lg font-semibold text-gray-900">Cash register report</h3>
            <p class="mt-2 text-sm text-gray-500">Review register totals, cash flow, and transaction volume.</p>
        </a>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Expense Summary</h3>
            <div class="mt-4 space-y-3 text-gray-700">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <span>Total Expense Records</span>
                    <span>{{ number_format($totalExpenses) }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <span>Total Expense Amount</span>
                    <span>${{ number_format($expenseTotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <span>Maintenance Actual Cost</span>
                    <span>${{ number_format($maintenanceActualCost, 2) }}</span>
                </div>
                <div class="flex items-center justify-between pt-3 text-sm text-gray-500">
                    <span>Branch</span>
                    <span>{{ $branchId ? optional($branches->firstWhere('id', $branchId))->name : 'All branches' }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Maintenance Averages</h3>
            <div class="mt-4 space-y-3 text-gray-700">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <span>Average Completion</span>
                    <span>{{ number_format($averageMaintenanceTime, 2) }} hrs</span>
                </div>
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <span>Total Maintenance Requests</span>
                    <span>{{ number_format($totalMaintenanceRequests) }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Inventory Alerts</h3>
            <div class="mt-4 space-y-3 text-gray-700">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <span>Items Needing Reorder</span>
                    <span>{{ number_format($reorderAlerts->count()) }}</span>
                </div>
                @foreach ($reorderAlerts as $item)
                    <div class="rounded-2xl bg-gray-50 px-4 py-3 text-sm text-gray-700">
                        <div class="font-medium">{{ $item->name }}</div>
                        <div>On hand: {{ number_format($item->quantity_on_hand, 2) }} · Reorder at
                            {{ number_format($item->reorder_level, 2) }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Top Selling Menu Items</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-700">
                @foreach ($topMenuItems as $item)
                    <div class="rounded-2xl border border-gray-100 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $item->item_name }}</p>
                                <p class="text-xs text-gray-500">Qty sold: {{ number_format($item->total_qty, 2) }}</p>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-900">${{ number_format($item->total_revenue, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Top Categories by Revenue</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-700">
                @foreach ($topCategories as $category)
                    <div class="rounded-2xl border border-gray-100 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $category->category_name }}</p>
                                <p class="text-xs text-gray-500">Total units:
                                    {{ number_format($category->total_qty, 2) }}</p>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-900">${{ number_format($category->total_revenue, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Top Customers</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-700">
                @foreach ($topCustomers as $customer)
                    <div class="rounded-2xl border border-gray-100 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ optional($customer->customer)->name ?? 'Guest' }}</p>
                                <p class="text-xs text-gray-500">Orders: {{ number_format($customer->orders) }}</p>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-900">${{ number_format($customer->spent, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Top Staff Performance</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-700">
                @foreach ($topStaff as $staff)
                    <div class="rounded-2xl border border-gray-100 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ optional($staff->creator)->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">Orders: {{ number_format($staff->orders) }}</p>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-900">${{ number_format($staff->revenue, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Top Suppliers by Stock Value</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-700">
                @foreach ($topSuppliers as $supplier)
                    <div class="rounded-2xl border border-gray-100 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ optional($supplier->supplier)->name ?? 'Supplier' }}</p>
                                <p class="text-xs text-gray-500">Inventory items:
                                    {{ number_format($supplier->item_count) }}</p>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-900">${{ number_format($supplier->value_on_hand, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Expense Category Breakdown</h3>
            <div class="mt-4 space-y-3 text-sm text-gray-700">
                @foreach ($expenseCategories as $category)
                    <div class="rounded-2xl border border-gray-100 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ optional($category->category)->name ?? 'Uncategorized' }}</p>
                                <p class="text-xs text-gray-500">Expense total by category</p>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-900">${{ number_format($category->total_amount, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Maintenance Status Breakdown</h3>
            <div class="mt-4 space-y-3">
                @foreach (['requested', 'approved', 'in_progress', 'completed', 'rejected', 'cancelled'] as $status)
                    <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-3">
                        <span
                            class="text-sm font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $status)) }}</span>
                        <span
                            class="text-sm font-semibold text-gray-900">{{ number_format($maintenanceStatusCounts[$status] ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">Recent Maintenance</h4>
                    <div class="mt-3 space-y-3">
                        @forelse ($recentMaintenance as $request)
                            <div class="rounded-2xl border border-gray-100 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $request->equipment_name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ optional($request->branch)->name ?? 'No branch' }}</p>
                                    </div>
                                    <span
                                        class="text-xs font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $request->status)) }}</span>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    {{ $request->requested_date?->format('M d, Y') ?? '—' }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No recent maintenance requests.</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-900">Recent Expenses</h4>
                    <div class="mt-3 space-y-3">
                        @forelse ($recentExpenses as $expense)
                            <div class="rounded-2xl border border-gray-100 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $expense->title }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ optional($expense->branch)->name ?? 'No branch' }}</p>
                                    </div>
                                    <span
                                        class="text-xs font-semibold text-gray-900">${{ number_format($expense->amount, 2) }}</span>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    {{ $expense->expense_date?->format('M d, Y') ?? '—' }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No recent expenses recorded.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
