<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Daily Production Costs</h1>
                <p class="text-gray-500">Manage daily production cost entries and lock production dates.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Production Cost</x-ui.button>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <div class="panel xl:col-span-2">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                    <x-ui.input name="search" wire:model.live="search" placeholder="Search production costs..." />
                    <x-ui.select name="filterBranch" wire:model="filterBranch">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="filterModule" wire:model="filterModule">
                        <option value="">All Modules</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input type="date" name="filterDate" wire:model="filterDate" />
                </div>

                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Branch</th>
                            <th>Module</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailyProductionCosts as $cost)
                            <tr>
                                <td>{{ $cost->title }}</td>
                                <td>{{ $cost->production_date->format('Y-m-d') }}</td>
                                <td>{{ number_format($cost->amount, 2) }}</td>
                                <td>{{ $cost->branch?->name }}</td>
                                <td>{{ $cost->module?->name }}</td>
                                <td class="text-center">
                                    @if ($cost->is_locked)
                                        <span class="badge bg-danger">Locked</span>
                                    @else
                                        <span class="badge bg-success">Open</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <x-ui.table-dropdown>
                                        <x-ui.table-dropdown-item icon="pencil-square"
                                            wire:click="edit({{ $cost->id }})">Edit</x-ui.table-dropdown-item>
                                        @if ($canManageLocks)
                                            @if ($cost->is_locked)
                                                <x-ui.table-dropdown-item icon="lock-open"
                                                    wire:click="unlockCost({{ $cost->id }})">Unlock</x-ui.table-dropdown-item>
                                            @else
                                                <x-ui.table-dropdown-item icon="lock-closed"
                                                    wire:click="lockCost({{ $cost->id }})">Lock</x-ui.table-dropdown-item>
                                            @endif
                                        @endif
                                        <x-ui.table-dropdown-item danger icon="trash"
                                            wire:click="confirmDelete({{ $cost->id }})">Delete</x-ui.table-dropdown-item>
                                    </x-ui.table-dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-gray-500">No production costs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>

                <div class="mt-5">{{ $dailyProductionCosts->links() }}</div>
            </div>

            @if ($canManageLocks)
            <div class="panel">
                <h2 class="text-lg font-semibold mb-4">Production Day Lock</h2>

                <div class="space-y-4">
                    <x-ui.select label="Branch" name="lockBranch" wire:model="lockBranch">
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Module" name="lockModule" wire:model="lockModule">
                        <option value="">Select Module</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input type="date" label="Production Date" name="lockProductionDate"
                        wire:model="lockProductionDate" />

                    @if ($productionDayLock)
                        <div class="rounded-lg border border-warning/40 bg-warning-light p-4 text-sm text-yellow-800">
                            Locked by {{ $productionDayLock->locker?->name ?? 'Unknown' }} on
                            {{ $productionDayLock->locked_at?->format('Y-m-d H:i') }}.
                        </div>
                    @endif

                    <div class="flex justify-end gap-3">
                        <x-ui.button wire:click="toggleProductionDayLock" icon="lock-closed">Toggle Lock</x-ui.button>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <x-ui.modal name="daily-production-cost-form" maxWidth="4xl">
            <x-slot:title>{{ $costId ? 'Edit Production Cost' : 'Add Production Cost' }}</x-slot:title>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.select label="Branch" name="branch_id" wire:model="branch_id">
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Module" name="module_id" wire:model="module_id">
                        <option value="">Select Module</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input type="date" label="Production Date" name="production_date"
                        wire:model="production_date" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input label="Title" name="title" wire:model.live="title" />
                    <x-ui.input label="Amount" name="amount" type="number" step="0.01" wire:model="amount" />
                </div>

                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />
                @if ($canManageLocks)
                    <x-ui.checkbox label="Lock this entry" name="is_locked" wire:model="is_locked" />
                @endif
            </div>

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger"
                        x-on:click="$dispatch('close-modal', 'daily-production-cost-form')">Cancel</x-ui.button>
                    <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                        Cost</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        @foreach ($dailyProductionCosts as $cost)
            <x-ui.modal name="delete-production-cost-{{ $cost->id }}" maxWidth="sm">
                <x-slot:title>Delete Production Cost</x-slot:title>
                <p>Confirm deletion of <strong>{{ $cost->title }}</strong>?</p>
                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-secondary"
                            x-on:click="$dispatch('close-modal', 'delete-production-cost-{{ $cost->id }}')">Cancel</x-ui.button>
                        <x-ui.button wire:click="delete({{ $cost->id }})" variant="danger">Delete</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        @endforeach
    </div>
</div>
