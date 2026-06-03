<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Discount Management</h1>
                <p class="text-gray-500">Create active discount rules that POS operators can apply at checkout.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Discount</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search discounts..." />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterModule" wire:model.live="filterModule">
                    <option value="">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </x-ui.select>
                <div></div>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Branch</th>
                        <th>Module</th>
                        <th>Value</th>
                        <th>Rules</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($discounts as $discount)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $discount->name }}</div>
                                @if ($discount->code)
                                    <div class="text-xs text-gray-500">Code: {{ $discount->code }}</div>
                                @endif
                            </td>
                            <td>{{ $discount->branch?->name }}</td>
                            <td>{{ $discount->module?->name }}</td>
                            <td>
                                {{ $discount->type === 'percentage' ? number_format($discount->value, 2) . '%' : number_format($discount->value, 2) }}
                            </td>
                            <td class="text-xs text-gray-500">
                                Min {{ number_format($discount->minimum_bill_amount, 2) }}
                                @if ($discount->maximum_discount_amount)
                                    / Max {{ number_format($discount->maximum_discount_amount, 2) }}
                                @endif
                            </td>
                            <td>
                                @if ($discount->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $discount->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger icon="trash" wire:click="confirmDelete({{ $discount->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-500">No discount rules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $discounts->links() }}</div>
        </div>

        <x-ui.modal name="discount-form" maxWidth="5xl">
            <x-slot:title>{{ $discountId ? 'Edit Discount' : 'Add Discount' }}</x-slot:title>

            <div class="space-y-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.select label="Branch" name="branch_id" wire:model.live="branch_id">
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
                    <x-ui.input label="Discount Name" name="name" wire:model.live="name" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <x-ui.input label="Code" name="code" wire:model="code" placeholder="Optional" />
                    <x-ui.select label="Type" name="type" wire:model.live="type">
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </x-ui.select>
                    <x-ui.input label="Value" type="number" name="value" wire:model="value" min="0.01" step="0.0001" />
                    <x-ui.input label="Minimum Bill" type="number" name="minimum_bill_amount" wire:model="minimum_bill_amount" min="0" step="0.01" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.input label="Maximum Discount" type="number" name="maximum_discount_amount" wire:model="maximum_discount_amount" min="0" step="0.01" placeholder="Optional" />
                    <x-ui.input label="Starts At" type="datetime-local" name="starts_at" wire:model="starts_at" />
                    <x-ui.input label="Ends At" type="datetime-local" name="ends_at" wire:model="ends_at" />
                </div>

                <x-ui.textarea label="Description" name="description" wire:model="description" />
                <x-ui.checkbox label="Active Discount" name="is_active" wire:model="is_active" />
            </div>

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'discount-form')">Cancel</x-ui.button>
                    <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save Discount</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        @foreach ($discounts as $discount)
            <x-ui.modal name="delete-discount-{{ $discount->id }}" maxWidth="sm">
                <x-slot:title>Delete Discount</x-slot:title>
                <p>Are you sure you want to delete <strong>{{ $discount->name }}</strong>?</p>
                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-secondary" x-on:click="$dispatch('close-modal', 'delete-discount-{{ $discount->id }}')">Cancel</x-ui.button>
                        <x-ui.button wire:click="delete({{ $discount->id }})" variant="danger">Delete</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        @endforeach
    </div>
</div>
