<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Expenses</h1>
                <p class="text-gray-500">Track branch expenses and category spend.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Expense</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search expenses..." />
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
                <x-ui.select name="filterCategory" wire:model="filterCategory">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input type="date" name="filterDate" wire:model="filterDate" />
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Branch</th>
                        <th>Module</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->title }}</td>
                            <td>{{ $expense->category?->name }}</td>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($expense->amount, 2) }}</td>
                            <td>{{ $expense->branch?->name }}</td>
                            <td>{{ $expense->module?->name }}</td>
                            <td class="text-center">
                                @if ($expense->is_locked)
                                    <span class="badge bg-danger">Locked</span>
                                @else
                                    <span class="badge bg-success">Open</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square"
                                        wire:click="edit({{ $expense->id }})">Edit</x-ui.table-dropdown-item>
                                    @if ($canManageLocks)
                                        @if ($expense->is_locked)
                                            <x-ui.table-dropdown-item icon="lock-open"
                                                wire:click="unlockExpense({{ $expense->id }})">Unlock</x-ui.table-dropdown-item>
                                        @else
                                            <x-ui.table-dropdown-item icon="lock-closed"
                                                wire:click="lockExpense({{ $expense->id }})">Lock</x-ui.table-dropdown-item>
                                        @endif
                                    @endif
                                    <x-ui.table-dropdown-item danger icon="trash"
                                        wire:click="confirmDelete({{ $expense->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-500">No expenses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $expenses->links() }}</div>
        </div>

        <x-ui.modal name="expense-form" maxWidth="4xl">
            <x-slot:title>{{ $expenseId ? 'Edit Expense' : 'Add Expense' }}</x-slot:title>

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
                    <x-ui.select label="Category" name="expense_category_id" wire:model="expense_category_id">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.input type="date" label="Expense Date" name="expense_date" wire:model="expense_date" />
                    <x-ui.input label="Title" name="title" wire:model.live="title" />
                    <x-ui.input label="Amount" name="amount" type="number" step="0.01" wire:model="amount" />
                </div>

                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />
            </div>

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger"
                        x-on:click="$dispatch('close-modal', 'expense-form')">Cancel</x-ui.button>
                    <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                        Expense</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        @foreach ($expenses as $expense)
            <x-ui.modal name="delete-expense-{{ $expense->id }}" maxWidth="sm">
                <x-slot:title>Delete Expense</x-slot:title>
                <p>Confirm deletion of <strong>{{ $expense->title }}</strong>?</p>
                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-secondary"
                            x-on:click="$dispatch('close-modal', 'delete-expense-{{ $expense->id }}')">Cancel</x-ui.button>
                        <x-ui.button wire:click="delete({{ $expense->id }})" variant="danger">Delete</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        @endforeach
    </div>
</div>
