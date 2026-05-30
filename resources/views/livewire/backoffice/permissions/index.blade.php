<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Permission Management</h1>
                <p class="text-gray-500">Create permissions and assign them to roles.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Permission</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search permissions..." />
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Roles</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td class="max-w-xl truncate">
                                {{ $permission->roles->pluck('name')->join(', ') ?: 'No roles' }}
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item
                                        wire:click="edit({{ $permission->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger wire:click="delete({{ $permission->id }})"
                                        wire:confirm="Are you sure?">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-10 text-gray-500">No permissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $permissions->links() }}</div>
        </div>

        <x-ui.modal name="permission-form" maxWidth="xl">
            <x-slot:title>{{ $permissionId ? 'Edit Permission' : 'Create Permission' }}</x-slot:title>
            <div class="space-y-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.input label="Permission Name" name="name" wire:model="name" />
                    <x-ui.input label="Guard Name" name="guard_name" wire:model="guard_name" />
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">Assign to Roles</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($roles as $role)
                            <label
                                class="flex items-center gap-3 rounded-3xl border border-slate-200 bg-white px-4 py-3">
                                <input type="checkbox" wire:model="assignedRoles" value="{{ $role->id }}"
                                    class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" />
                                <span class="text-sm text-slate-700">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'permission-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Permission</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>
    </div>
</div>
