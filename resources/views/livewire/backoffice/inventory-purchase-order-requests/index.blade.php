<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Purchase Order Requests</h1>
                <p class="text-gray-500">Inventory purchase requests approved into stock purchases and adjustment history.</p>
            </div>
            @if ($canSubmit)
                <x-ui.button icon="plus" wire:click="create" target="create">New Purchase Request</x-ui.button>
            @endif
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
                <x-ui.select name="filterStatus" wire:model.live="filterStatus">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'approved', 'rejected', 'cancelled'] as $status)
                        <option value="{{ $status }}">{{ str($status)->headline() }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>

        <div class="panel">
            <x-ui.table>
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Branch / Module</th>
                        <th>Supplier</th>
                        <th>Qty / Cost</th>
                        <th>Status</th>
                        <th>Requested By</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td>
                                <p class="font-semibold">{{ $request->inventoryItem?->name ?? 'Deleted item' }}</p>
                                <p class="text-xs text-gray-500">{{ $request->reference_no }}</p>
                                @if ($request->reason)<p class="text-xs text-gray-500">{{ $request->reason }}</p>@endif
                            </td>
                            <td>
                                <p>{{ $request->branch?->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $request->module?->name ?? '-' }}</p>
                            </td>
                            <td>{{ $request->supplier?->name ?? '-' }}</td>
                            <td>
                                <p class="font-semibold">{{ number_format($request->requested_qty, 2) }} @ {{ number_format($request->unit_cost, 2) }}</p>
                                <p class="text-xs text-gray-500">Total {{ number_format($request->total_cost, 2) }}</p>
                                @if ($request->quantity_before !== null)
                                    <p class="text-xs text-gray-500">{{ number_format($request->quantity_before, 2) }} to {{ number_format($request->quantity_after, 2) }}</p>
                                @endif
                            </td>
                            <td>
                                <span @class([
                                    'badge',
                                    'bg-warning' => $request->status === 'pending',
                                    'bg-success' => $request->status === 'approved',
                                    'bg-danger' => $request->status === 'rejected',
                                    'bg-secondary' => $request->status === 'cancelled',
                                ])>{{ str($request->status)->headline() }}</span>
                            </td>
                            <td>
                                <p>{{ $request->requester?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $request->requested_at?->format('M d, Y h:i A') }}</p>
                            </td>
                            <td class="text-center">
                                @if ($request->status === 'pending')
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @if ($canApprove)
                                            <x-ui.button size="sm" variant="success" icon="check" wire:click="approve({{ $request->id }})">Approve</x-ui.button>
                                            <x-ui.button size="sm" variant="danger" icon="x-mark" wire:click="reject({{ $request->id }})">Reject</x-ui.button>
                                        @endif
                                        @if ((int) $request->requested_by === (int) auth()->id())
                                            <x-ui.button size="sm" variant="secondary" icon="no-symbol" wire:click="cancel({{ $request->id }})">Cancel</x-ui.button>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-xs text-gray-500">{{ $request->approver?->name ? 'By '.$request->approver->name : 'Closed' }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-500">No purchase order requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $requests->links() }}</div>
        </div>

        <x-ui.modal name="inventory-purchase-order-request-form" maxWidth="4xl">
            <x-slot:title>Submit Purchase Order Request</x-slot:title>

            <div class="space-y-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.select label="Branch" name="branch_id" wire:model.live="branch_id">
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Module" name="module_id" wire:model.live="module_id" :disabled="! $branch_id">
                        <option value="">{{ $branch_id ? 'All / No module' : 'Select branch first' }}</option>
                        @foreach ($formModules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Inventory Item" name="inventory_item_id" wire:model.live="inventory_item_id" :disabled="! $branch_id">
                        <option value="">{{ $branch_id ? 'Select Item' : 'Select branch first' }}</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ number_format($item->quantity_on_hand ?? 0, 2) }} on hand)</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <x-ui.select label="Supplier" name="supplier_id" wire:model="supplier_id">
                        <option value="">No supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input label="Quantity" type="number" step="0.01" min="0.01" name="requested_qty" wire:model="requested_qty" />
                    <x-ui.input label="Unit Cost" type="number" step="0.01" min="0" name="unit_cost" wire:model="unit_cost" />
                    <x-ui.input label="Expected Delivery" type="date" name="expected_delivery_date" wire:model="expected_delivery_date" />
                </div>

                <x-ui.input label="Reason" name="reason" wire:model="reason" placeholder="Reorder, event demand, low stock..." />
                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'inventory-purchase-order-request-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="submit" target="submit" loadingText="Submitting..." icon="paper-airplane">Submit Request</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>
    </div>
</div>
