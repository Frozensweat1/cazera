<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Activity Logs</h1>
                <p class="text-gray-500">Track user sign-ins, sign-outs, and backoffice actions.</p>
            </div>
            <x-ui.button type="button" variant="secondary" wire:click="clearFilters">Reset Filters</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_180px_180px_180px] xl:grid-cols-[minmax(0,1fr)_170px_170px_170px_170px_150px_150px]">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search activity..." />

                <x-ui.select name="filterUser" wire:model.live="filterUser">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select name="filterModule" wire:model.live="filterModule">
                    <option value="">All modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select name="filterEvent" wire:model.live="filterEvent">
                    <option value="">All events</option>
                    @foreach ($events as $event)
                        <option value="{{ $event }}">{{ str($event)->headline() }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.input type="date" name="dateFrom" wire:model.live="dateFrom" />
                <x-ui.input type="date" name="dateTo" wire:model.live="dateTo" />
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Context</th>
                        <th>Route</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $log->logged_at?->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->logged_at?->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="font-semibold">{{ $log->user?->name ?? 'System' }}</div>
                                <div class="text-xs text-gray-500">{{ $log->user?->email }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ str($log->event)->headline() }}</span>
                                <p class="mt-1 max-w-[260px] text-xs text-gray-500">{{ $log->description }}</p>
                            </td>
                            <td>
                                <div class="text-sm">{{ $log->branch?->name ?? 'No branch' }}</div>
                                <div class="text-xs text-gray-500">{{ $log->module?->name ?? 'No module' }}</div>
                            </td>
                            <td>
                                <div class="max-w-[220px] truncate text-sm">{{ $log->route_name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $log->method }}</div>
                            </td>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-500">No activity logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $logs->links() }}</div>
        </div>
    </div>
</div>
