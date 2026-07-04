<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Net Revenue</h1>
                <p class="text-gray-500">Compare collected sales against sold item cost, production, maintenance, and operating expenses.</p>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
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
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Margin</p>
                    <p class="text-lg font-extrabold text-gray-950">{{ number_format($summary['margin'], 2) }}%</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-8">
            <div class="panel">
                <p class="text-sm text-gray-500">Gross Sales</p>
                <p class="mt-2 text-2xl font-extrabold text-gray-950"><x-ui.money :amount="$summary['gross_sales']" /></p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Collected Sales</p>
                <p class="mt-2 text-2xl font-extrabold text-emerald-700"><x-ui.money :amount="$summary['sales_collected']" /></p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Refunds</p>
                <p class="mt-2 text-2xl font-extrabold text-red-700"><x-ui.money :amount="$summary['refunds']" /></p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Item Cost</p>
                <p class="mt-2 text-2xl font-extrabold text-purple-700"><x-ui.money :amount="$summary['trackable_item_costs']" /></p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Production Costs</p>
                <p class="mt-2 text-2xl font-extrabold text-amber-700"><x-ui.money :amount="$summary['production_costs']" /></p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Maintenance</p>
                <p class="mt-2 text-2xl font-extrabold text-cyan-700"><x-ui.money :amount="$summary['maintenance_costs']" /></p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Expenses</p>
                <p class="mt-2 text-2xl font-extrabold text-orange-700"><x-ui.money :amount="$summary['expenses']" /></p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Net Revenue</p>
                <p @class([
                    'mt-2 text-2xl font-extrabold',
                    'text-emerald-700' => $summary['net_revenue'] >= 0,
                    'text-red-700' => $summary['net_revenue'] < 0,
                ])><x-ui.money :amount="$summary['net_revenue']" /></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="panel xl:col-span-2">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-gray-950">Branch & Module Performance</h2>
                    <p class="text-sm text-gray-500">Collected sales less sold item cost, production, maintenance, and expenses.</p>
                </div>

                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Module</th>
                            <th>Collected</th>
                            <th>Item Cost</th>
                            <th>Production</th>
                            <th>Maintenance</th>
                            <th>Expenses</th>
                            <th class="text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branchBreakdown as $row)
                            <tr>
                                <td>{{ $row['branch'] }}</td>
                                <td>{{ $row['module'] }}</td>
                                <td><x-ui.money :amount="$row['paid']" /></td>
                                <td><x-ui.money :amount="$row['item_cost']" /></td>
                                <td><x-ui.money :amount="$row['production']" /></td>
                                <td><x-ui.money :amount="$row['maintenance']" /></td>
                                <td><x-ui.money :amount="$row['expenses']" /></td>
                                <td @class([
                                    'text-right font-bold',
                                    'text-emerald-700' => $row['net'] >= 0,
                                    'text-red-700' => $row['net'] < 0,
                                ])><x-ui.money :amount="$row['net']" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-gray-500">No revenue records found for the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            <div class="space-y-6">
                <div class="panel">
                    <h2 class="text-lg font-bold text-gray-950">Recent Expenses</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentExpenses as $expense)
                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-950">{{ $expense->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $expense->category?->name }} / {{ $expense->expense_date?->format('M d, Y') }}</p>
                                    </div>
                                    <p class="font-bold text-orange-700"><x-ui.money :amount="$expense->amount" /></p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No recent expenses.</p>
                        @endforelse
                    </div>
                </div>

                <div class="panel">
                    <h2 class="text-lg font-bold text-gray-950">Recent Production Costs</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentProductionCosts as $cost)
                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-950">{{ $cost->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $cost->branch?->name }} / {{ $cost->production_date?->format('M d, Y') }}</p>
                                    </div>
                                    <p class="font-bold text-amber-700"><x-ui.money :amount="$cost->amount" /></p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No recent production costs.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
