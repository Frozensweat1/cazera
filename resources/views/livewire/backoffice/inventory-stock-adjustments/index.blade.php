<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Stock Adjustments</h1>
                <p class="text-gray-500">Record inventory adjustments and maintain audit history.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Adjustment</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search adjustments..." />
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
                <x-ui.select name="filterType" wire:model.live="filterType">
                    <option value="">All Types</option>
                    <option value="purchase">Purchase</option>
                    <option value="sale">Sale</option>
                    <option value="adjustment_increase">Increase</option>
                    <option value="adjustment_decrease">Decrease</option>
                    <option value="transfer_in">Transfer In</option>
                    <option value="transfer_out">Transfer Out</option>
                    <option value="wastage">Wastage</option>
                    <option value="manual_set">Manual Set</option>
                    <option value="opening_stock">Opening Stock</option>
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Branch</th>
                        <th>Type</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Change</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($adjustments as $adjustment)
                        <tr>
                            <td>
                                <p class="font-semibold">{{ $adjustment->inventoryItem?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $adjustment->reference_no }}</p>
                            </td>
                            <td>{{ $adjustment->branch?->name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $adjustment->type)) }}</td>
                            <td>{{ number_format($adjustment->quantity_before, 2) }}</td>
                            <td>{{ number_format($adjustment->quantity_after, 2) }}</td>
                            <td>{{ number_format($adjustment->change_qty, 2) }}</td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square"
                                        wire:click="edit({{ $adjustment->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger icon="trash"
                                        wire:click="confirmDelete({{ $adjustment->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-gray-500">No stock adjustments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $adjustments->links() }}</div>
        </div>

        <x-ui.modal name="inventory-stock-adjustment-form" maxWidth="4xl">
            <x-slot:title>{{ $adjustmentId ? 'Edit Stock Adjustment' : 'Create Stock Adjustment' }}</x-slot:title>
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
                    <x-ui.select label="Inventory Item" name="inventory_item_id" wire:model.live="inventory_item_id"
                        :disabled="! $module_id">
                        <option value="">{{ $module_id ? 'Select Item' : 'Select module first' }}</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.select label="Type" name="type" wire:model="type">
                        <option value="purchase">Purchase</option>
                        <option value="sale">Sale</option>
                        <option value="adjustment_increase">Increase</option>
                        <option value="adjustment_decrease">Decrease</option>
                        <option value="transfer_in">Transfer In</option>
                        <option value="transfer_out">Transfer Out</option>
                        <option value="wastage">Wastage</option>
                        <option value="manual_set">Manual Set</option>
                        <option value="opening_stock">Opening Stock</option>
                    </x-ui.select>
                    <x-ui.input label="Quantity Before" type="number" name="quantity_before"
                        wire:model="quantity_before" step="0.01" />
                    <x-ui.input label="Quantity After" type="number" name="quantity_after" wire:model="quantity_after"
                        step="0.01" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.input label="Reference" name="reference_no" wire:model="reference_no" />
                    <x-ui.input label="Reason" name="reason" wire:model="reason" />
                    <x-ui.input label="Transaction Date" name="transaction_date" type="date"
                        wire:model="transaction_date" />
                </div>

                <x-ui.select label="Performed By" name="performed_by" wire:model="performed_by">
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'inventory-stock-adjustment-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Adjustment</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>

    </div>
</div>
