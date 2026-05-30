<div>
    <div class="space-y-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold">
                    Branch Management
                </h1>

                <p class="text-gray-500">
                    Manage all operational branches
                </p>
            </div>

            <x-ui.button icon="plus" wire:click="create">
                Add Branch
            </x-ui.button>

        </div>

        <!-- TABLE -->
        <div class="panel">

            <div class="mb-5">

                <x-ui.input name="search" wire:model.live="search" placeholder="Search branches..." />

            </div>

            <x-ui.table>

                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Location</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($branches as $branch)
                        <tr>

                            <td>

                                <div>
                                    <p class="font-semibold">
                                        {{ $branch->name }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        {{ $branch->slug }}
                                    </p>
                                </div>

                            </td>

                            <td>
                                <span class="text-gray-700 dark:text-gray-200">
                                    {{ $branch->location ?: 'Not provided' }}
                                </span>
                            </td>

                            <td>{{ $branch->phone ?: 'Not provided' }}</td>

                            <td>{{ $branch->email ?: 'Not provided' }}</td>

                            <td>

                                @if ($branch->is_active)
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

                                    <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $branch->id }})">
                                        Edit
                                    </x-ui.table-dropdown-item>

                                    <x-ui.table-dropdown-item danger wire:click="delete({{ $branch->id }})">
                                        Delete
                                    </x-ui.table-dropdown-item>

                                </x-ui.table-dropdown>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center py-10 text-gray-500">
                                No branches found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </x-ui.table>

            <div class="mt-5">
                {{ $branches->links() }}
            </div>

        </div>

        <!-- MODAL -->
        <x-ui.modal name="branch-form" maxWidth="2xl">

            <x-slot:title>
                {{ $branchId ? 'Edit Branch' : 'Create Branch' }}
            </x-slot:title>

            <div class="space-y-5">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <x-ui.input label="Branch Name" name="name" wire:model.live="name" />

                    <x-ui.input label="Slug" name="slug" wire:model="slug" />

                    <x-ui.input label="Phone" name="phone" wire:model="phone" />

                    <x-ui.input label="Email" name="email" type="email" wire:model="email" />

                </div>

                <x-ui.input label="Location" name="location" wire:model="location" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <x-ui.input label="Latitude" name="latitude" wire:model="latitude" />
                        <button type="button"
                            class="mt-2 inline-flex items-center gap-1.5 rounded-lg px-1 text-xs font-semibold text-primary transition hover:text-primary/80 focus:outline-none focus:ring-2 focus:ring-primary/20"
                            x-data
                            @click="
                                navigator.geolocation.getCurrentPosition(
                                    (pos) => {
                                        $wire.set('latitude', pos.coords.latitude);
                                        $wire.set('longitude', pos.coords.longitude);
                                    },
                                    (err) => {
                                        alert('Unable to get location: ' + err.message);
                                    }
                                )
                            "
                        >
                            <x-heroicon-o-map-pin class="h-4 w-4" />
                            Use current location
                        </button>
                    </div>

                    <x-ui.input label="Longitude" name="longitude" wire:model="longitude" />

                </div>

                <x-ui.checkbox label="Active Branch" name="is_active" wire:model="is_active" />

                <x-slot:footer>

                    <div class="flex justify-end gap-3">

                        <x-ui.button variant="outline-danger" x-on:click="$dispatch('close-modal', 'branch-form')">
                            Cancel
                        </x-ui.button>

                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                            Save Branch
                        </x-ui.button>

                    </div>

                </x-slot:footer>

            </div>

        </x-ui.modal>

    </div>

</div>
