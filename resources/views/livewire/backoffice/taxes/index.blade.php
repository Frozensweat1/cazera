<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Tax Management</h1>
                <p class="text-gray-500">Manage display-only tax rates for POS receipts and sales transparency.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Tax</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search taxes..." />
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
                        <th>Rate</th>
                        <th>Availability</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taxes as $tax)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $tax->name }}</div>
                                @if ($tax->description)
                                    <div class="text-xs text-gray-500">{{ str($tax->description)->limit(80) }}</div>
                                @endif
                            </td>
                            <td>{{ $tax->branch?->name }}</td>
                            <td>{{ $tax->module?->name }}</td>
                            <td>{{ number_format($tax->rate_percent, 2) }}%</td>
                            <td class="text-xs text-gray-500">
                                {{ $tax->starts_at?->format('M d, Y') ?? 'Always' }}
                                -
                                {{ $tax->ends_at?->format('M d, Y') ?? 'No end' }}
                            </td>
                            <td>
                                @if ($tax->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $tax->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger icon="trash" wire:click="confirmDelete({{ $tax->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-500">No tax rules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $taxes->links() }}</div>
        </div>

        <x-ui.modal name="tax-form" maxWidth="4xl">
            <x-slot:title>{{ $taxId ? 'Edit Tax' : 'Add Tax' }}</x-slot:title>

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
                    <x-ui.input label="Tax Name" name="name" wire:model.live="name" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.input label="Rate (%)" type="number" name="rate_percent" wire:model="rate_percent" min="0" max="100" step="0.0001" />
                    <x-ui.input label="Starts At" type="datetime-local" name="starts_at" wire:model="starts_at" />
                    <x-ui.input label="Ends At" type="datetime-local" name="ends_at" wire:model="ends_at" />
                </div>

                <x-ui.textarea label="Description" name="description" wire:model="description" />
                <x-ui.checkbox label="Active Tax" name="is_active" wire:model="is_active" />
            </div>

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'tax-form')">Cancel</x-ui.button>
                    <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save Tax</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        @foreach ($taxes as $tax)
            <x-ui.modal name="delete-tax-{{ $tax->id }}" maxWidth="sm">
                <x-slot:title>Delete Tax</x-slot:title>
                <p>Are you sure you want to delete <strong>{{ $tax->name }}</strong>?</p>
                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-secondary" x-on:click="$dispatch('close-modal', 'delete-tax-{{ $tax->id }}')">Cancel</x-ui.button>
                        <x-ui.button wire:click="delete({{ $tax->id }})" variant="danger">Delete</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        @endforeach
    </div>
</div>
