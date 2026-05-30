<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Inventory Stock</h1>
                <p class="text-gray-500">Track stock balances by item and location.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">New Stock Record</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search stock records..." />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterItem" wire:model.live="filterItem">
                    <option value="">All Items</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterLocation" wire:model.live="filterLocation">
                    <option value="">All Locations</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Location</th>
                        <th>On Hand</th>
                        <th>Reserved</th>
                        <th>Reorder</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stocks as $stock)
                        <tr>
                            <td>{{ $stock->inventoryItem?->name ?? 'Unknown Item' }}</td>
                            <td>{{ $stock->inventoryLocation?->name ?? 'Unassigned' }}</td>
                            <td>{{ number_format($stock->quantity_on_hand, 2) }}</td>
                            <td>{{ number_format($stock->quantity_reserved, 2) }}</td>
                            <td>{{ number_format($stock->reorder_level, 2) }} /
                                {{ number_format($stock->reorder_quantity, 2) }}</td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square"
                                        wire:click="edit({{ $stock->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger icon="trash"
                                        wire:click="confirmDelete({{ $stock->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">No stock records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $stocks->links() }}</div>
        </div>

        <x-ui.modal name="inventory-item-stock-form" maxWidth="4xl">
            <x-slot:title>{{ $stockId ? 'Edit Stock Record' : 'Create Stock Record' }}</x-slot:title>
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.select label="Inventory Item" name="inventory_item_id" wire:model="inventory_item_id">
                        <option value="">Select Item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Location" name="inventory_location_id" wire:model="inventory_location_id">
                        <option value="">Select Location</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-ui.input label="Quantity On Hand" name="quantity_on_hand" type="number"
                        wire:model="quantity_on_hand" step="0.01" />
                    <x-ui.input label="Quantity Reserved" name="quantity_reserved" type="number"
                        wire:model="quantity_reserved" step="0.01" />
                    <x-ui.input label="Reorder Level" name="reorder_level" type="number" wire:model="reorder_level"
                        step="0.01" />
                    <x-ui.input label="Reorder Quantity" name="reorder_quantity" type="number"
                        wire:model="reorder_quantity" step="0.01" />
                </div>

                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'inventory-item-stock-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Stock</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>

    </div>
</div>
