<div>
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Inventory Categories</h1>
                <p class="text-gray-500">Organize product inventory by category and parent grouping.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Category</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search inventory categories..." />
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
                        <th>Category</th>
                        <th>Parent</th>
                        <th>Branch</th>
                        <th>Module</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ $category->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $category->slug }}</p>
                                </div>
                            </td>
                            <td>{{ $category->parent?->name ?? '—' }}</td>
                            <td>{{ $category->branch?->name }}</td>
                            <td>{{ $category->module?->name }}</td>
                            <td>
                                @if ($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square"
                                        wire:click="edit({{ $category->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger icon="trash"
                                        wire:click="confirmDelete({{ $category->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">No inventory categories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $categories->links() }}</div>
        </div>

        <x-ui.modal name="inventory-category-form" maxWidth="3xl">
            <x-slot:title>{{ $categoryId ? 'Edit Inventory Category' : 'Create Inventory Category' }}</x-slot:title>
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

                    <x-ui.select label="Parent Category" name="parent_id" wire:model="parent_id">
                        <option value="">No Parent</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input label="Category Name" name="name" wire:model.live="name" />
                    <x-ui.input label="Slug" name="slug" wire:model="slug" />
                </div>

                <x-ui.textarea label="Description" name="description" wire:model="description" />
                <x-ui.checkbox label="Active Category" name="is_active" wire:model="is_active" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'inventory-category-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Category</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>

    </div>
</div>
