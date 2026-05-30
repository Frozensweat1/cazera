<div>
    <div class="space-y-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold">
                    Module Management
                </h1>

                <p class="text-gray-500">
                    Manage POS and Activity modules
                </p>
            </div>

            <x-ui.button icon="plus" wire:click="create">
                Add Module
            </x-ui.button>

        </div>

        <!-- TABLE -->
        <div class="panel">

            <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_260px]">

                <x-ui.input name="search" wire:model.live="search" placeholder="Search modules..." />

                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All branches</option>

                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </x-ui.select>

            </div>

            <x-ui.table>

                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Branch</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($modules as $module)
                        <tr>

                            <td>

                                <div>
                                    <p class="font-semibold">
                                        {{ $module->name }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        {{ $module->slug }}
                                    </p>
                                </div>

                            </td>

                            <td>
                                {{ $module->branch?->name }}
                            </td>

                            <td>

                                @if ($module->type === 'pos')
                                    <span class="badge bg-primary">
                                        POS
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        Activity
                                    </span>
                                @endif

                            </td>

                            <td>

                                @if ($module->is_active)
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Inactive
                                    </span>
                                @endif

                            </td>

                            <td class="text-center">

                                <x-ui.table-dropdown>

                                    <x-ui.table-dropdown-item icon="pencil-square"
                                        wire:click="edit({{ $module->id }})">
                                        Edit
                                    </x-ui.table-dropdown-item>

                                    <x-ui.table-dropdown-item danger icon="trash"
                                        wire:click="delete({{ $module->id }})">
                                        Delete
                                    </x-ui.table-dropdown-item>

                                </x-ui.table-dropdown>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="text-center py-10 text-gray-500">
                                No modules found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </x-ui.table>

            <div class="mt-5">
                {{ $modules->links() }}
            </div>

        </div>

        <!-- MODAL -->
        <x-ui.modal name="module-form" maxWidth="2xl">

            <x-slot:title>
                {{ $moduleId ? 'Edit Module' : 'Create Module' }}
            </x-slot:title>

            <div class="space-y-5">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <x-ui.select label="Branch" name="branch_id" wire:model="branch_id">

                        <option value="">
                            Select Branch
                        </option>

                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ $branch->name }}
                            </option>
                        @endforeach

                    </x-ui.select>

                    <x-ui.select label="Module Type" name="type" wire:model.live="type">

                        <option value="pos">
                            POS
                        </option>

                        <option value="activity">
                            Activity
                        </option>

                    </x-ui.select>

                    <x-ui.input label="Module Name" name="name" wire:model.live="name" />

                    <x-ui.input label="Slug" name="slug" wire:model="slug" />

                </div>

                <x-ui.textarea label="Description" name="description" wire:model="description" />

                <!-- DYNAMIC TYPE INFO -->

                @if ($type === 'pos')
                    <div class="panel border border-primary/20 bg-primary-light">

                        <h3 class="font-semibold mb-2">
                            POS Module
                        </h3>

                        <p class="text-sm text-gray-600">
                            This module will operate as a POS sales outlet.
                        </p>

                    </div>
                @endif

                @if ($type === 'activity')
                    <div class="panel border border-warning/20 bg-warning-light">

                        <h3 class="font-semibold mb-2">
                            Activity Module
                        </h3>

                        <p class="text-sm text-gray-600">
                            This module supports activity booking and reservations.
                        </p>

                    </div>
                @endif

                <x-ui.checkbox label="Active Module" name="is_active" wire:model="is_active" />

                <x-slot:footer>

                    <div class="flex justify-end gap-3">

                        <x-ui.button variant="outline-danger" x-on:click="$dispatch('close-modal', 'module-form')">
                            Cancel
                        </x-ui.button>

                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                            Save Module
                        </x-ui.button>

                    </div>

                </x-slot:footer>

            </div>

        </x-ui.modal>

    </div>
</div>
