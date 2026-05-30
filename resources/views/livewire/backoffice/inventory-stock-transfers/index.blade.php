<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Stock Transfers</h1>
                <p class="text-gray-500">Manage transfers between branches and warehouses.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">New Transfer</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search transfers..." />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterStatus" wire:model.live="filterStatus">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfers as $transfer)
                        <tr>
                            <td>
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ $transfer->inventoryItem?->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $transfer->reference_no }}</p>
                                </div>
                            </td>
                            <td>{{ $transfer->fromBranch?->name }}</td>
                            <td>{{ $transfer->toBranch?->name }}</td>
                            <td>{{ number_format($transfer->quantity, 2) }}</td>
                            <td>
                                <span
                                    class="badge {{ $transfer->status === 'completed' ? 'bg-success' : ($transfer->status === 'cancelled' ? 'bg-danger' : 'bg-warning') }}">{{ ucfirst($transfer->status) }}</span>
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    @if ($transfer->status === 'pending')
                                        @if (auth()->user()?->canAccessBranch($transfer->to_branch_id))
                                            <x-ui.table-dropdown-item icon="check"
                                                wire:click="confirmComplete({{ $transfer->id }})">Complete</x-ui.table-dropdown-item>
                                        @endif
                                        <x-ui.table-dropdown-item icon="pencil-square"
                                            wire:click="edit({{ $transfer->id }})">Edit</x-ui.table-dropdown-item>
                                        @if (auth()->user()?->canAccessBranch($transfer->from_branch_id))
                                            <x-ui.table-dropdown-item danger icon="x-mark"
                                                wire:click="confirmCancel({{ $transfer->id }})">Cancel</x-ui.table-dropdown-item>
                                        @endif
                                        @if (auth()->user()?->canAccessBranch($transfer->from_branch_id))
                                            <x-ui.table-dropdown-item danger icon="trash"
                                                wire:click="confirmDelete({{ $transfer->id }})">Delete</x-ui.table-dropdown-item>
                                        @endif
                                    @else
                                        <span class="px-3 py-2 text-xs text-gray-500">No actions</span>
                                    @endif
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">No stock transfers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $transfers->links() }}</div>
        </div>

        <x-ui.modal name="inventory-stock-transfer-form" maxWidth="5xl">
            <x-slot:title>{{ $transferId ? 'Edit Stock Transfer' : 'Create Stock Transfer' }}</x-slot:title>
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.select label="From Branch" name="from_branch_id" wire:model.live="from_branch_id">
                        <option value="">Select From Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Inventory Item" name="inventory_item_id" wire:model.live="inventory_item_id">
                        <option value="">Select Item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="To Branch" name="to_branch_id" wire:model.live="to_branch_id">
                        <option value="">Select To Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.select label="Destination Module" name="destination_module_id"
                        wire:model.live="destination_module_id" :disabled="! $to_branch_id">
                        <option value="">{{ $to_branch_id ? 'Select Module' : 'Select destination branch first' }}</option>
                        @foreach ($destinationModules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Destination Category" name="destination_category_id"
                        wire:model="destination_category_id" :disabled="! $destination_module_id">
                        <option value="">{{ $destination_module_id ? 'Select Category' : 'Select destination module first' }}</option>
                        @foreach ($destinationCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Destination Supplier" name="destination_supplier_id"
                        wire:model="destination_supplier_id" :disabled="! $to_branch_id">
                        <option value="">No Supplier</option>
                        @foreach ($destinationSuppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input label="Quantity" type="number" name="quantity" wire:model="quantity" step="0.01" />
                    <x-ui.input label="Transfer Date" type="date" name="transfer_date" wire:model="transfer_date" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input label="Reference" name="reference_no" wire:model="reference_no" />
                    <x-ui.input label="Reason" name="reason" wire:model="reason" />
                </div>

                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'inventory-stock-transfer-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Transfer</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>

    </div>
</div>
