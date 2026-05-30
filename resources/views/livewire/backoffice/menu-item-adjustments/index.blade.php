<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold">Menu Item Adjustments</h1>
                <p class="text-gray-500">Record and audit stock movements for menu items and products.</p>
            </div>

            <div class="shrink-0 sm:pt-1">
                <x-ui.button icon="plus" wire:click="create" target="create">
                    Add Adjustment
                </x-ui.button>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search item, ref, reason..." />

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
                    @foreach ($types as $typeOption)
                        <option value="{{ $typeOption }}">{{ str($typeOption)->replace('_', ' ')->title() }}</option>
                    @endforeach
                    <option value="sale">Sale</option>
                </x-ui.select>
            </div>
        </div>

        <div class="panel">
            <x-ui.table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Branch</th>
                        <th>Module</th>
                        <th>Type</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Change</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($adjustments as $adjustment)
                        <tr>
                            <td>
                                <p class="font-semibold">{{ $adjustment->menuItem?->name ?? 'Deleted item' }}</p>
                                <p class="text-xs text-gray-500">{{ $adjustment->reference_no ?: $adjustment->sale?->sale_number ?: '-' }}</p>
                            </td>
                            <td>{{ $adjustment->branch?->name ?? '-' }}</td>
                            <td>{{ $adjustment->module?->name ?? '-' }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'bg-success' => in_array($adjustment->type, ['purchase', 'refund', 'adjustment_increase', 'transfer_in', 'production', 'opening_stock'], true),
                                    'bg-danger' => in_array($adjustment->type, ['sale', 'adjustment_decrease', 'transfer_out', 'wastage'], true),
                                    'bg-info' => $adjustment->type === 'manual_set',
                                ])>
                                    {{ str($adjustment->type)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td>{{ number_format($adjustment->quantity_before, 2) }}</td>
                            <td>{{ number_format($adjustment->quantity_after, 2) }}</td>
                            <td>
                                <span @class([
                                    'font-semibold',
                                    'text-success' => $adjustment->change_qty > 0,
                                    'text-danger' => $adjustment->change_qty < 0,
                                    'text-gray-500' => (float) $adjustment->change_qty === 0.0,
                                ])>
                                    {{ number_format($adjustment->change_qty, 2) }}
                                </span>
                            </td>
                            <td>{{ $adjustment->transaction_date?->format('M d, Y') }}</td>
                            <td class="text-center">
                                @if ($adjustment->sale_id)
                                    <span class="text-xs font-semibold text-gray-500">POS record</span>
                                @else
                                    <x-ui.table-dropdown>
                                        <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $adjustment->id }})">
                                            Edit
                                        </x-ui.table-dropdown-item>
                                        <x-ui.table-dropdown-item danger icon="trash" wire:click="delete({{ $adjustment->id }})">
                                            Delete
                                        </x-ui.table-dropdown-item>
                                    </x-ui.table-dropdown>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-gray-500">
                                No menu item adjustments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">
                {{ $adjustments->links() }}
            </div>
        </div>

        <x-ui.modal name="menu-item-adjustment-form" maxWidth="3xl">
            <x-slot:title>
                {{ $adjustmentId ? 'Edit Menu Item Adjustment' : 'Create Menu Item Adjustment' }}
            </x-slot:title>

            <div class="space-y-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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

                    <x-ui.select label="Menu Item" name="menu_item_id" wire:model.live="menu_item_id" :disabled="! $module_id">
                        <option value="">{{ $module_id ? 'Select Menu Item' : 'Select module first' }}</option>
                        @foreach ($formMenuItems as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} (Qty: {{ number_format($item->quantity ?? 0, 2) }})</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <x-ui.select label="Type" name="type" wire:model="type">
                        @foreach ($types as $typeOption)
                            <option value="{{ $typeOption }}">{{ str($typeOption)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.input label="Quantity Before" type="number" step="0.01" name="quantity_before"
                        wire:model="quantity_before" readonly />

                    <x-ui.input label="Quantity After" type="number" step="0.01" name="quantity_after"
                        wire:model="quantity_after" />

                    <x-ui.input label="Transaction Date" type="date" name="transaction_date"
                        wire:model="transaction_date" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input label="Reference" name="reference_no" wire:model="reference_no" />
                    <x-ui.input label="Reason" name="reason" wire:model="reason" />
                </div>

                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'menu-item-adjustment-form')">
                            Cancel
                        </x-ui.button>

                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                            Save Adjustment
                        </x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>
    </div>
</div>
