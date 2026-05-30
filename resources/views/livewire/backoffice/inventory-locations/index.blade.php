<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Inventory Locations</h1>
                <p class="text-gray-500">Manage warehouses, stores, and inventory locations.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Inventory Location</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search locations..." />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterModule" wire:model.live="filterModule">
                    <option value="">All Modules</option>
                    @foreach ($filterModules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Branch / Module</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $location)
                        <tr>
                            <td>
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ $location->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $location->code ?? '—' }}</p>
                                </div>
                            </td>
                            <td>{{ ucfirst($location->type) }}</td>
                            <td>
                                <div class="space-y-1">
                                    <p>{{ $location->branch?->name ?? 'No Branch' }}</p>
                                    <p class="text-xs text-gray-500">{{ $location->module?->name ?? 'No Module' }}</p>
                                </div>
                            </td>
                            <td>
                                @if ($location->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square"
                                        wire:click="edit({{ $location->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger icon="trash"
                                        wire:click="confirmDelete({{ $location->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-500">No inventory locations found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $locations->links() }}</div>
        </div>

        <x-ui.modal name="inventory-location-form" maxWidth="4xl">
            <x-slot:title>{{ $locationId ? 'Edit Inventory Location' : 'Create Inventory Location' }}</x-slot:title>
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.select label="Branch" name="branch_id" wire:model.live="branch_id">
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Module" name="module_id" wire:model="module_id" :disabled="! $branch_id">
                        <option value="">{{ $branch_id ? 'Select Module' : 'Select branch first' }}</option>
                        @foreach ($formModules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Location Type" name="type" wire:model="type">
                        <option value="warehouse">Warehouse</option>
                        <option value="store">Store</option>
                        <option value="freezer">Freezer</option>
                        <option value="manufacturing">Manufacturing</option>
                        <option value="other">Other</option>
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input label="Name" name="name" wire:model.live="name" />
                    <x-ui.input label="Code" name="code" wire:model="code" />
                </div>

                <x-ui.textarea label="Address" name="address" wire:model="address" />
                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />
                <x-ui.checkbox label="Active Location" name="is_active" wire:model="is_active" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'inventory-location-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Location</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>

    </div>
</div>
