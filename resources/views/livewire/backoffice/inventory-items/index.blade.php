<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Inventory Items</h1>
                <p class="text-gray-500">Track products, stock levels, and pricing.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Inventory Item</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search inventory items..." />
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
                    <x-ui.select name="filterStatus" wire:model.live="filterStatus">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventoryItems as $inventoryItem)
                        <tr>
                            <td>
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ $inventoryItem->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $inventoryItem->sku ?? $inventoryItem->slug }}
                                    </p>
                                </div>
                            </td>
                            <td>{{ $inventoryItem->category?->name }}</td>
                            <td>{{ $inventoryItem->supplier?->name }}</td>
                            <td>{{ number_format($inventoryItem->quantity_on_hand, 2) }}</td>
                            <td>
                                @if ($inventoryItem->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square"
                                        wire:click="edit({{ $inventoryItem->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger icon="trash"
                                        wire:click="confirmDelete({{ $inventoryItem->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">No inventory items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $inventoryItems->links() }}</div>
        </div>

        <x-ui.modal name="inventory-item-form" maxWidth="5xl">
            <x-slot:title>{{ $itemId ? 'Edit Inventory Item' : 'Create Inventory Item' }}</x-slot:title>
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.select label="Branch" name="branch_id" wire:model.live="branch_id">
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Module" name="module_id" wire:model.live="module_id" :disabled="! $branch_id">
                        <option value="">{{ $branch_id ? 'Select Module' : 'Select branch first' }}</option>
                        @foreach ($formModules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Category" name="category_id" wire:model="category_id" :disabled="! $module_id">
                        <option value="">{{ $module_id ? 'Select Category' : 'Select module first' }}</option>
                        @foreach ($formCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Supplier" name="supplier_id" wire:model="supplier_id">
                        <option value="">Select Supplier</option>
                        @foreach ($formSuppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input label="Item Name" name="name" wire:model.live="name" />
                    <x-ui.input label="Slug" name="slug" wire:model="slug" />
                    <x-ui.input label="SKU" name="sku" wire:model="sku" />
                    <x-ui.input label="Barcode" name="barcode" wire:model="barcode" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.input label="Unit Cost" name="unit_cost" type="number" wire:model="unit_cost"
                        step="0.01" />
                    <x-ui.input label="Unit Price" name="unit_price" type="number" wire:model="unit_price"
                        step="0.01" />
                    <div class="flex items-center pt-8">
                        <x-ui.checkbox label="Track Inventory" name="is_trackable" wire:model="is_trackable" />
                    </div>
                </div>

                <x-ui.textarea label="Description" name="description" wire:model="description" />
                <x-ui.checkbox label="Active Item" name="is_active" wire:model="is_active" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'inventory-item-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Item</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>

    </div>
</div>
