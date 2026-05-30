<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Finance Report</h2>
                <p class="text-sm text-gray-500">Collected revenue, cost pressure, estimated profit, and module-level contribution.</p>
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
            <div class="panel"><p class="text-sm text-gray-500">Gross Sales</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($revenue, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Collected</p><p class="mt-2 text-2xl font-extrabold text-emerald-700">{{ number_format($collectedRevenue, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Operating Cost</p><p class="mt-2 text-2xl font-extrabold text-amber-700">{{ number_format($operatingCost, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Net Estimate</p><p @class(['mt-2 text-2xl font-extrabold', 'text-emerald-700' => $profitEstimate >= 0, 'text-red-700' => $profitEstimate < 0])>{{ number_format($profitEstimate, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Margin</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($profitMargin, 2) }}%</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Refunds</p><p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($refunds, 2) }}</p></div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Cost Composition</h3>
                <div class="mt-4 space-y-3">
                    <div class="flex justify-between rounded-lg bg-slate-50 p-3"><span>Expenses</span><strong>{{ number_format($expenseTotal, 2) }}</strong></div>
                    <div class="flex justify-between rounded-lg bg-slate-50 p-3"><span>Production Costs</span><strong>{{ number_format($productionCostTotal, 2) }}</strong></div>
                    <div class="flex justify-between rounded-lg bg-slate-50 p-3"><span>Maintenance Actual</span><strong>{{ number_format($maintenanceActualCost, 2) }}</strong></div>
                </div>
            </div>

            <div class="panel xl:col-span-2">
                <h3 class="text-lg font-bold text-gray-900">Module Contribution</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-gray-500"><tr><th class="py-2">Module</th><th>Orders</th><th>Collected</th><th>Costs</th><th class="text-right">Net</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($moduleProfitability as $row)
                                <tr><td class="py-3 font-semibold">{{ $row['module'] }}</td><td>{{ number_format($row['orders']) }}</td><td>{{ number_format($row['collected'], 2) }}</td><td>{{ number_format($row['costs'], 2) }}</td><td @class(['text-right font-bold', 'text-emerald-700' => $row['net'] >= 0, 'text-red-700' => $row['net'] < 0])>{{ number_format($row['net'], 2) }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-gray-500">No module contribution data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Expense Category Pressure</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($expenseCategories as $category)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="font-semibold">{{ $category->category?->name ?? 'Uncategorized' }}</span><strong>{{ number_format($category->total_amount, 2) }}</strong></div>
                    @empty
                        <p class="text-sm text-gray-500">No expense category data.</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Production Cost by Branch</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($productionByBranch as $branch)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="font-semibold">{{ $branch->branch?->name ?? 'Branch' }}</span><strong>{{ number_format($branch->total_amount, 2) }}</strong></div>
                    @empty
                        <p class="text-sm text-gray-500">No production cost data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
