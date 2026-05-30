<div>
    <div class="space-y-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">
                    Branch Staff Assignments
                </h1>

                <p class="text-gray-500">
                    Manage staff assignments to branches
                </p>
            </div>

            <x-ui.button icon="plus" wire:click="create">
                Add Assignment
            </x-ui.button>
        </div>

        <!-- TABLE -->
        <div class="panel">

            <div class="mb-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="grid w-full grid-cols-1 gap-4 md:max-w-2xl md:grid-cols-2">
                        <x-ui.input name="search" wire:model.live="search" placeholder="Search staff by name or email..." />

                        <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    @if(count($selected) > 0)
                        <div class="flex justify-end">
                            <x-ui.button variant="outline-danger" icon="trash" wire:click="confirmBulkDelete">
                                Delete Selected ({{ count($selected) }})
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Staff Member</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Assigned At</th>
                        <th>Assigned By</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" wire:model.live="selected" value="{{ $assignment->id }}" class="rounded">
                            </td>
                            <td>
                                <div>
                                    <p class="font-semibold">{{ $assignment->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $assignment->user->email }}</p>
                                </div>
                            </td>
                            <td>{{ $assignment->branch->name }}</td>
                            <td>
                                <span class="badge {{ $assignment->is_active ? 'bg-success' : 'bg-gray-500' }}">
                                    {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $assignment->assigned_at?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>{{ $assignment->assignedBy?->name ?? 'System' }}</td>
                            <td class="text-center">

                                <x-ui.table-dropdown>

                                    <x-ui.table-dropdown-item wire:click="edit({{ $assignment->id }})" icon="pencil">
                                        Edit
                                    </x-ui.table-dropdown-item>

                                    <x-ui.table-dropdown-item wire:click="delete({{ $assignment->id }})" icon="trash" danger>
                                        Remove
                                    </x-ui.table-dropdown-item>

                                </x-ui.table-dropdown>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-gray-500">
                                No staff assignments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <x-ui.modal name="branch-staff-form" maxWidth="md">

        <x-slot:title>
            {{ $assignmentId ? 'Edit' : 'New' }} Branch Assignment
        </x-slot:title>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Branch</label>
                <x-ui.select name="branch_id" wire:model.live="branch_id">
                    <option value="">Select a branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                @error('branch_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Staff Member</label>
                <x-ui.select name="user_id" wire:model="user_id" :disabled="! $branch_id">
                    <option value="">
                        {{ $branch_id ? 'Select a user' : 'Select branch first' }}
                    </option>
                    @foreach($formUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </x-ui.select>
                @error('user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <x-ui.checkbox label="Active Status" name="is_active" wire:model="is_active" />
        </div>

        <x-slot:footer>
            <div class="flex justify-end gap-3">

                <x-ui.button variant="outline-danger" x-on:click="$dispatch('close-modal', 'branch-staff-form')">
                    Cancel
                </x-ui.button>

                <x-ui.button wire:click="save" icon="check">
                    Save Assignment
                </x-ui.button>

            </div>
        </x-slot:footer>

    </x-ui.modal>

</div>
