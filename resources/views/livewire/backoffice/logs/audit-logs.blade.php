<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Audit Logs</h1>
                <p class="text-gray-500">Review created, updated, and deleted records with before/after values.</p>
            </div>
            <x-ui.button type="button" variant="secondary" wire:click="clearFilters">Reset Filters</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_180px_180px_180px] xl:grid-cols-[minmax(0,1fr)_160px_160px_160px_160px_170px_145px_145px]">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search audit trail..." />

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

                <x-ui.select name="filterModel" wire:model.live="filterModel">
                    <option value="">All models</option>
                    @foreach ($models as $model)
                        <option value="{{ $model }}">{{ class_basename($model) }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.input type="date" name="dateFrom" wire:model.live="dateFrom" />
                <x-ui.input type="date" name="dateTo" wire:model.live="dateTo" />
            </div>

            <div class="space-y-4">
                @forelse ($logs as $log)
                    <article class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-[#0e1726]">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span @class([
                                        'badge',
                                        'bg-success' => $log->event === 'created',
                                        'bg-warning' => $log->event === 'updated',
                                        'bg-danger' => $log->event === 'deleted',
                                        'bg-primary' => ! in_array($log->event, ['created', 'updated', 'deleted'], true),
                                    ])>{{ str($log->event)->headline() }}</span>
                                    <span class="font-semibold text-slate-900 dark:text-white-light">{{ class_basename($log->auditable_type) }}</span>
                                    <span class="text-sm text-gray-500">#{{ $log->auditable_id }}</span>
                                </div>
                                <h2 class="mt-2 text-lg font-bold">{{ $log->auditable_label }}</h2>
                                <p class="text-sm text-gray-500">
                                    {{ $log->user?->name ?? 'System' }} / {{ $log->branch?->name ?? 'No branch' }} / {{ $log->module?->name ?? 'No module' }}
                                </p>
                            </div>
                            <div class="text-sm text-gray-500 lg:text-right">
                                <div>{{ $log->logged_at?->format('M d, Y h:i A') }}</div>
                                <div>{{ $log->ip_address ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-2">
                            <div class="rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-500">Before</p>
                                @forelse (($log->old_values ?? []) as $field => $value)
                                    <div class="grid grid-cols-[130px_minmax(0,1fr)] gap-3 border-t border-slate-200 py-2 text-sm first:border-t-0 dark:border-slate-700">
                                        <span class="font-semibold">{{ str($field)->headline() }}</span>
                                        <span class="break-words text-gray-600 dark:text-gray-300">{{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No previous values.</p>
                                @endforelse
                            </div>

                            <div class="rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-500">After</p>
                                @forelse (($log->new_values ?? []) as $field => $value)
                                    <div class="grid grid-cols-[130px_minmax(0,1fr)] gap-3 border-t border-slate-200 py-2 text-sm first:border-t-0 dark:border-slate-700">
                                        <span class="font-semibold">{{ str($field)->headline() }}</span>
                                        <span class="break-words text-gray-600 dark:text-gray-300">{{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No new values.</p>
                                @endforelse
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-gray-500 dark:border-slate-700">
                        No audit logs found.
                    </div>
                @endforelse
            </div>

            <div class="mt-5">{{ $logs->links() }}</div>
        </div>
    </div>
</div>
