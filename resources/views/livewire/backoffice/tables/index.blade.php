<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Tables</h1>
                <p class="text-gray-500">Manage dining tables and availability by branch.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Table</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search tables..." />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Table</th>
                        <th>Branch</th>
                        <th>Seats</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tables as $table)
                        <tr>
                            <td>
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ $table->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $table->slug ?? '-' }}</p>
                                </div>
                            </td>
                            <td>{{ $table->branch?->name ?? 'No Branch' }}</td>
                            <td>{{ $table->seats ? number_format($table->seats) : '-' }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'bg-success' => $table->status === 'available',
                                    'bg-warning' => $table->status === 'reserved',
                                    'bg-danger' => $table->status === 'occupied',
                                ])>{{ ucfirst($table->status) }}</span>
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $table->id }})">
                                        Edit
                                    </x-ui.table-dropdown-item>
                                    @if ($table->status !== 'available')
                                        <x-ui.table-dropdown-item icon="check-circle" wire:click="release({{ $table->id }})">
                                            Release
                                        </x-ui.table-dropdown-item>
                                    @endif
                                    <x-ui.table-dropdown-item danger icon="trash" wire:click="confirmDelete({{ $table->id }})">
                                        Delete
                                    </x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-gray-500">No tables found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $tables->links() }}</div>
        </div>

        <x-ui.modal name="table-form" maxWidth="3xl">
            <x-slot:title>{{ $tableId ? 'Edit Table' : 'Create Table' }}</x-slot:title>

            <div class="space-y-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.select label="Branch" name="branch_id" wire:model="branch_id">
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.input label="Table Name" name="name" wire:model="name" placeholder="Table 12" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input label="Seats" name="seats" type="number" min="1" wire:model="seats" placeholder="4" />
                    <x-ui.select label="Status" name="status" wire:model="status">
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="reserved">Reserved</option>
                    </x-ui.select>
                </div>

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'table-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                            Save Table
                        </x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>
    </div>
</div>
