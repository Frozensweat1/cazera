<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Role Management</h1>
                <p class="text-gray-500">Create roles and assign permissions to each role.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Role</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search roles..." />
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Permissions</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td class="max-w-xl truncate">
                                {{ $role->permissions->pluck('name')->join(', ') ?: 'No permissions' }}
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item
                                        wire:click="edit({{ $role->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger wire:click="delete({{ $role->id }})"
                                        wire:confirm="Are you sure?">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-10 text-gray-500">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $roles->links() }}</div>
        </div>

        <x-ui.modal name="role-form" maxWidth="xl">
            <x-slot:title>{{ $roleId ? 'Edit Role' : 'Create Role' }}</x-slot:title>
            <div class="space-y-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.input label="Role Name" name="name" wire:model="name" />
                    <x-ui.input label="Guard Name" name="guard_name" wire:model="guard_name" />
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">Permissions</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($permissions as $permission)
                            <label
                                class="flex items-center gap-3 rounded-3xl border border-slate-200 bg-white px-4 py-3">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->id }}"
                                    class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" />
                                <span class="text-sm text-slate-700">{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'role-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Role</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>
    </div>
</div>
