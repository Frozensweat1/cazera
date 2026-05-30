<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Expense Categories</h1>
                <p class="text-gray-500">Manage expense categories for each branch and module.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Category</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search categories..." />
                <x-ui.select name="filterBranch" wire:model="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterModule" wire:model="filterModule">
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
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenseCategories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
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
                            <td colspan="5" class="text-center py-10 text-gray-500">No expense categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $expenseCategories->links() }}</div>
        </div>

        <x-ui.modal name="expense-category-form" maxWidth="4xl">
            <x-slot:title>{{ $categoryId ? 'Edit Expense Category' : 'Add Expense Category' }}</x-slot:title>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <x-ui.input label="Category Name" name="name" wire:model.live="name" />
                </div>

                <x-ui.textarea label="Description" name="description" wire:model="description" />
                <x-ui.checkbox label="Active Category" name="is_active" wire:model="is_active" />
            </div>

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger"
                        x-on:click="$dispatch('close-modal', 'expense-category-form')">Cancel</x-ui.button>
                    <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                        Category</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        @foreach ($expenseCategories as $category)
            <x-ui.modal name="delete-expense-category-{{ $category->id }}" maxWidth="sm">
                <x-slot:title>Delete Expense Category</x-slot:title>
                <p>Are you sure you want to delete <strong>{{ $category->name }}</strong>?</p>
                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-secondary"
                            x-on:click="$dispatch('close-modal', 'delete-expense-category-{{ $category->id }}')">Cancel</x-ui.button>
                        <x-ui.button wire:click="delete({{ $category->id }})" variant="danger">Delete</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        @endforeach
    </div>
</div>
