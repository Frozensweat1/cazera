<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Maintenance Report</h2>
                <p class="text-sm text-gray-500">Backlog, urgency, completion speed, cost variance, and asset workload.</p>
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
            <div class="panel"><p class="text-sm text-gray-500">Requests</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($totalRequests) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Open</p><p class="mt-2 text-2xl font-extrabold text-amber-700">{{ number_format($openRequests) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Urgent Open</p><p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($urgentOpenRequests) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Overdue</p><p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($overdueRequests) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Completion Rate</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($completionRate, 2) }}%</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Locked</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($lockedRequests) }}</p></div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Cost Control</h3>
                <div class="mt-4 space-y-3">
                    <div class="flex justify-between rounded-lg bg-slate-50 p-3"><span>Estimated</span><strong>{{ number_format($estimatedCost, 2) }}</strong></div>
                    <div class="flex justify-between rounded-lg bg-slate-50 p-3"><span>Actual</span><strong>{{ number_format($actualCost, 2) }}</strong></div>
                    <div class="flex justify-between rounded-lg bg-slate-50 p-3"><span>Variance</span><strong @class(['text-emerald-700' => $costVariance <= 0, 'text-red-700' => $costVariance > 0])>{{ number_format($costVariance, 2) }}</strong></div>
                </div>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Average Completion</h3>
                <p class="mt-4 text-3xl font-extrabold">{{ number_format($averageCompletion, 2) }} hrs</p>
                <p class="mt-2 text-sm text-gray-500">Average request-to-completion time.</p>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Status Mix</h3>
                <div class="mt-4 space-y-2">
                    @foreach (['requested', 'approved', 'in_progress', 'completed', 'rejected', 'cancelled'] as $status)
                        <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span><strong>{{ number_format($statusCounts[$status] ?? 0) }}</strong></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Modules by Maintenance Load</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($topModules as $module)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><div><p class="font-semibold">{{ $module->module?->name ?? 'No module' }}</p><p class="text-xs text-gray-500">Requests {{ number_format($module->total_requests) }}</p></div><strong>{{ number_format($module->total_cost, 2) }}</strong></div>
                    @empty
                        <p class="text-sm text-gray-500">No module maintenance summary.</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Recent Requests</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($recentRequests as $request)
                        <div class="rounded-lg border border-slate-200 p-3"><div class="flex justify-between"><strong>{{ $request->equipment_name }}</strong><span>{{ ucfirst(str_replace('_', ' ', $request->status)) }}</span></div><p class="text-xs text-gray-500">{{ $request->branch?->name ?? 'No branch' }} / {{ $request->requested_date?->format('M d, Y') ?? 'N/A' }}</p></div>
                    @empty
                        <p class="text-sm text-gray-500">No recent requests.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
