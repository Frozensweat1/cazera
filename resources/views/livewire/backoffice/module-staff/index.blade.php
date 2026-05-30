<div>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}

    <div class="space-y-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-2xl font-bold">
                    Module Staff
                </h1>

                <p class="text-gray-500">
                    Assign staff to operational modules
                </p>

            </div>

            <div class="flex items-center gap-3">

                <x-ui.button color="danger" icon="trash" wire:click="confirmBulkDelete">
                    Bulk Delete
                </x-ui.button>

                <x-ui.button icon="plus" wire:click="create">
                    Assign Staff
                </x-ui.button>

            </div>

        </div>

        <!-- FILTERS -->
        <div class="panel">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <x-ui.input name="search" wire:model.live="search" placeholder="Search staff..." />

                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select name="filterModule" wire:model.live="filterModule">

                    <option value="">
                        All Modules
                    </option>

                    @foreach ($filterModules as $module)
                        <option value="{{ $module->id }}">
                            {{ $module->name }}
                        </option>
                    @endforeach

                </x-ui.select>

            </div>

        </div>

        <!-- TABLE -->
        <div class="panel">

            <x-ui.table>

                <thead>

                    <tr>

                        <th width="50"></th>

                        <th>User</th>

                        <th>Branch</th>

                        <th>Module</th>

                        <th>Assigned By</th>

                        <th>Status</th>

                        <th>Date</th>

                        <th class="text-center">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse ($assignments as $assignment)
                        <tr>

                            <td>

                                <input type="checkbox" value="{{ $assignment->id }}" wire:model.live="selected">

                            </td>

                            <td>

                                <div>

                                    <p class="font-semibold">
                                        {{ $assignment->user?->name }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        {{ $assignment->user?->email }}
                                    </p>

                                </div>

                            </td>

                            <td>
                                {{ $assignment->branch?->name }}
                            </td>

                            <td>
                                {{ $assignment->module?->name }}
                            </td>

                            <td>
                                {{ $assignment->assignedBy?->name ?? 'System' }}
                            </td>

                            <td>

                                @if ($assignment->is_active)
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Inactive
                                    </span>
                                @endif

                            </td>

                            <td>
                                {{ $assignment->assigned_at?->format('d M Y') }}
                            </td>

                            <td class="text-center">

                                <x-ui.table-dropdown>

                                    <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $assignment->id }})">
                                        Edit
                                    </x-ui.table-dropdown-item>

                                    <x-ui.table-dropdown-item danger icon="trash" wire:click="delete({{ $assignment->id }})">
                                        Delete
                                    </x-ui.table-dropdown-item>

                                </x-ui.table-dropdown>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center py-10 text-gray-500">
                                No assignments found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </x-ui.table>

            <div class="mt-5">

                {{ $assignments->links() }}

            </div>

        </div>

        <!-- FORM MODAL -->
        <x-ui.modal name="module-staff-form" maxWidth="2xl">

            <x-slot:title>

                {{ $assignmentId ? 'Edit Assignment' : 'Assign Staff To Module' }}

            </x-slot:title>

            <div class="space-y-5">

                <!-- USER + BRANCH + MODULE -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <x-ui.select label="Branch" name="branch_id" wire:model.live="branch_id">
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>

                    <!-- USER -->
                    <x-ui.select label="Staff User" name="user_id" wire:model="user_id" :disabled="! $branch_id">

                        <option value="">
                            {{ $branch_id ? 'Select User' : 'Select branch first' }}
                        </option>

                        @foreach ($formUsers as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} - {{ $user->email }}
                            </option>
                        @endforeach

                    </x-ui.select>

                    </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- MODULE -->
                    <x-ui.select label="Module" name="module_id" wire:model="module_id" :disabled="! $branch_id">

                        <option value="">
                            {{ $branch_id ? 'Select Module' : 'Select branch first' }}
                        </option>

                        @foreach ($formModules as $module)
                            <option value="{{ $module->id }}">
                                {{ $module->name }}
                            </option>
                        @endforeach

                    </x-ui.select>

                </div>

                <!-- STATUS -->
                <div class="pt-2">

                    <x-ui.checkbox label="Assignment Active" name="is_active" wire:model="is_active" />

                </div>

            </div>

            <x-slot:footer>

                <div class="flex justify-end gap-3">

                    <!-- CANCEL -->
                    <x-ui.button type="button" variant="outline-secondary"
                        x-on:click="$dispatch('close-modal', 'module-staff-form')">
                        Cancel
                    </x-ui.button>

                    <!-- SAVE -->
                    <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                        Save Assignment
                    </x-ui.button>

                </div>

            </x-slot:footer>

        </x-ui.modal>

    </div>

</div>
