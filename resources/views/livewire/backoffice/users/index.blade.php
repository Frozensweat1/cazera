<div>
    <div class="space-y-6">

        <!-- PAGE HEADER -->
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold">
                    User Management
                </h1>

                <p class="text-gray-500">
                    Manage system users and roles
                </p>
            </div>

            <x-ui.button icon="plus" wire:click="create">
                Add User
            </x-ui.button>

        </div>

        <!-- TABLE -->
        <div class="panel">

            <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_260px_260px]">

                <x-ui.input name="search" wire:model.live="search" placeholder="Search users..." />

                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All branches</option>

                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </x-ui.select>

                <x-ui.select name="filterModule" wire:model.live="filterModule">
                    <option value="">All modules</option>

                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">
                            {{ $module->name }}
                        </option>
                    @endforeach
                </x-ui.select>

            </div>

            <x-ui.table>

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Branches</th>
                        <th>Modules</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($users as $user)
                        <tr>

                            <td>
                                <div class="flex items-center gap-3">

                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}"
                                        class="w-10 h-10 rounded-full">

                                    <div>
                                        <p class="font-semibold">
                                            {{ $user->name }}
                                        </p>

                                        <p class="text-xs text-gray-500">
                                            {{ $user->address }}
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>{{ $user->phone }}</td>

                            <td>
                                <x-ui.status-badge :status="$user->roles->first()?->name ?? 'No Role'" />
                            </td>

                            <td>
                                <div class="max-w-[220px] text-sm text-gray-600 dark:text-gray-300">
                                    {{ $user->branchAssignments->pluck('branch.name')->filter()->unique()->take(3)->implode(', ') ?: 'No branches' }}
                                    @if ($user->branchAssignments->pluck('branch.name')->filter()->unique()->count() > 3)
                                        <span class="text-xs text-gray-400">+{{ $user->branchAssignments->pluck('branch.name')->filter()->unique()->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="max-w-[220px] text-sm text-gray-600 dark:text-gray-300">
                                    {{ $user->moduleAssignments->pluck('module.name')->filter()->unique()->take(3)->implode(', ') ?: 'No modules' }}
                                    @if ($user->moduleAssignments->pluck('module.name')->filter()->unique()->count() > 3)
                                        <span class="text-xs text-gray-400">+{{ $user->moduleAssignments->pluck('module.name')->filter()->unique()->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-center">

                                <x-ui.table-dropdown>

                                    <x-ui.table-dropdown-item wire:click="edit({{ $user->id }})">
                                        Edit
                                    </x-ui.table-dropdown-item>

                                    <x-ui.table-dropdown-item danger wire:click="delete({{ $user->id }})">
                                        Delete
                                    </x-ui.table-dropdown-item>

                                </x-ui.table-dropdown>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="text-center py-10 text-gray-500">
                                No users found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </x-ui.table>

            <div class="mt-5">
                {{ $users->links() }}
            </div>

        </div>

        <!-- MODAL -->
        <x-ui.modal name="user-form" maxWidth="xl">

            <x-slot:title>
                {{ $userId ? 'Edit User' : 'Create User' }}
            </x-slot:title>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <x-ui.input label="Name" name="name" wire:model="name" />

                    <x-ui.input label="Email" name="email" type="email" wire:model="email" />

                    <x-ui.input label="Phone" name="phone" wire:model="phone" />

                    <x-ui.select label="Role" name="role" wire:model="role">

                        <option value="">
                            Select Role
                        </option>

                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">
                                {{ $role->name }}
                            </option>
                        @endforeach

                    </x-ui.select>

                </div>

                <x-ui.textarea label="Address" name="address" wire:model="address" />

                <x-ui.input label="Password" name="password" type="password" wire:model="password" />

                <x-slot:footer>

                    <div class="flex justify-end gap-3">

                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'user-form')">
                            Cancel
                        </x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                            Save User
                        </x-ui.button>

                    </div>

                </x-slot:footer>

            </div>

        </x-ui.modal>

    </div>
</div>
