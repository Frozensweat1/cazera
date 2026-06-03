<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Production Cost Report</h2>
                <p class="text-sm text-gray-500">Daily cost discipline, locked records, and branch/module production spend.</p>
            </div>
            <x-reports.pdf-export-button :href="route('backoffice.reports.pdf', [
                'report' => 'production-costs',
                'filterBranch' => $filterBranch,
                'filterModule' => $filterModule,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ])" />
        </div>

        <div class="panel report-controls">
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

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="panel"><p class="text-sm text-gray-500">Total Cost</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($totalProductionCost, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Avg Entry</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($averageCost, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Daily Average</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($dailyAverage, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Locked / Open</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($lockedCount) }}</p><p class="text-xs text-gray-500">Open {{ number_format($pendingCount) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Last Updated</p><p class="mt-2 text-2xl font-extrabold">{{ $lastUpdated ? $lastUpdated->format('M d') : 'N/A' }}</p></div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Cost by Module</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($productionByModule as $module)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><div><p class="font-semibold">{{ $module->module?->name ?? 'No module' }}</p><p class="text-xs text-gray-500">Entries {{ number_format($module->entries) }}</p></div><strong>{{ number_format($module->total_amount, 2) }}</strong></div>
                    @empty
                        <p class="text-sm text-gray-500">No module cost data.</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Cost by Branch</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($productionByBranch as $branch)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="font-semibold">{{ $branch->branch?->name ?? 'Branch' }}</span><strong>{{ number_format($branch->total_amount, 2) }}</strong></div>
                    @empty
                        <p class="text-sm text-gray-500">No branch cost data.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Daily Trend</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($dailyTrend as $day)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><div><p class="font-semibold">{{ \Illuminate\Support\Carbon::parse($day->production_date)->format('M d, Y') }}</p><p class="text-xs text-gray-500">Entries {{ number_format($day->entries) }}</p></div><strong>{{ number_format($day->total_amount, 2) }}</strong></div>
                    @empty
                        <p class="text-sm text-gray-500">No daily cost trend.</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Recent Entries</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($recentCostEntries as $entry)
                        <div class="rounded-lg border border-slate-200 p-3"><div class="flex justify-between"><strong>{{ $entry->title }}</strong><span>{{ number_format($entry->amount, 2) }}</span></div><p class="text-xs text-gray-500">{{ $entry->branch?->name ?? 'Branch' }} / {{ $entry->production_date?->format('M d, Y') }} / {{ $entry->is_locked ? 'Locked' : 'Open' }}</p></div>
                    @empty
                        <p class="text-sm text-gray-500">No recent production costs.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
