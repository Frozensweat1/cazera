<div>
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Suppliers</h1>
                <p class="text-gray-500">Manage supplier records and contact details.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Supplier</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search suppliers..." />
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Branch</th>
                        <th>Module</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ $supplier->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $supplier->code ?? $supplier->slug }}</p>
                                </div>
                            </td>
                            <td>{{ $supplier->branch?->name }}</td>
                            <td>{{ $supplier->module?->name }}</td>
                            <td>
                                <div class="text-xs text-gray-500">
                                    <div>{{ $supplier->contact_name }}</div>
                                    <div>{{ $supplier->phone }}</div>
                                </div>
                            </td>
                            <td>
                                @if ($supplier->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square"
                                        wire:click="edit({{ $supplier->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger icon="trash"
                                        wire:click="confirmDelete({{ $supplier->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">No suppliers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $suppliers->links() }}</div>
        </div>

        <x-ui.modal name="supplier-form" maxWidth="2xl">
            <x-slot:title>{{ $supplierId ? 'Edit Supplier' : 'Create Supplier' }}</x-slot:title>
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                    <x-ui.input label="Supplier Name" name="name" wire:model.live="name" />
                    <x-ui.input label="Slug" name="slug" wire:model="slug" />
                    <x-ui.input label="Code" name="code" wire:model="code" />
                    <x-ui.input label="Contact Name" name="contact_name" wire:model="contact_name" />
                    <x-ui.input label="Email" name="email" wire:model="email" />
                    <x-ui.input label="Phone" name="phone" wire:model="phone" />
                </div>

                <x-ui.textarea label="Address" name="address" wire:model="address" />
                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />
                <x-ui.checkbox label="Active Supplier" name="is_active" wire:model="is_active" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'supplier-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Supplier</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>

        @foreach ($suppliers as $supplier)
            <x-ui.modal name="delete-supplier-{{ $supplier->id }}" maxWidth="sm">
                <x-slot:title>Delete Supplier</x-slot:title>
                <p>Are you sure you want to delete <strong>{{ $supplier->name }}</strong>?</p>
                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-secondary"
                            x-on:click="$dispatch('close-modal', 'delete-supplier-{{ $supplier->id }}')">Cancel</x-ui.button>
                        <x-ui.button wire:click="delete({{ $supplier->id }})" variant="danger">Delete</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        @endforeach
    </div>
</div>
