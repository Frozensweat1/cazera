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

                                    @if ($branch->is_active)
                                        <a href="{{ route('website.branch.show', $branch->slug) }}" target="_blank"
                                            rel="noopener"
                                            class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                                            <x-heroicon-o-arrow-top-right-on-square class="h-3.5 w-3.5" />
                                            Public page
                                        </a>
                                    @endif
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
        <x-ui.modal name="branch-form" maxWidth="3xl">

            <x-slot:title>
                {{ $branchId ? 'Edit Branch' : 'Create Branch' }}
            </x-slot:title>

            <div class="space-y-6">
                <section>
                    <div class="mb-4 flex items-center gap-2">
                        <x-heroicon-o-building-storefront class="h-5 w-5 text-primary" />
                        <h3 class="font-semibold text-slate-900 dark:text-white">Branch details</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-ui.input label="Branch Name" name="name" wire:model.blur="name" />
                        <x-ui.input label="Slug" name="slug" wire:model.defer="slug" />
                        <x-ui.input label="Phone" name="phone" wire:model.defer="phone" />
                        <x-ui.input label="WhatsApp" name="whatsapp" wire:model.defer="whatsapp" />
                        <x-ui.input label="Email" name="email" type="email" wire:model.defer="email" />
                        <x-ui.input label="Opening Hours" name="hours" wire:model.defer="hours"
                            placeholder="Mon-Sun, 10:00 AM - 11:00 PM" />
                        <div class="md:col-span-2">
                            <x-ui.input label="Location" name="location" wire:model.defer="location" />
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-200 pt-6 dark:border-white/10">
                    <div class="mb-4 flex items-center gap-2">
                        <x-heroicon-o-globe-alt class="h-5 w-5 text-primary" />
                        <h3 class="font-semibold text-slate-900 dark:text-white">Public profile</h3>
                    </div>
                    <div class="grid gap-4">
                        <x-ui.textarea label="Short Description" name="short_description"
                            wire:model.defer="short_description" rows="2" />
                        <x-ui.textarea label="Full Description" name="description" wire:model.defer="description"
                            rows="4" />
                        <x-ui.input label="Tags" name="tags_text" wire:model.defer="tags_text"
                            placeholder="Restaurant, Lounge, Events" />
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.input label="Map Embed URL" name="map_url" wire:model.defer="map_url" />
                            <x-ui.input label="Directions URL" name="directions_url"
                                wire:model.defer="directions_url" />
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-200 pt-6 dark:border-white/10">
                    <div class="mb-4 flex items-center gap-2">
                        <x-heroicon-o-photo class="h-5 w-5 text-primary" />
                        <h3 class="font-semibold text-slate-900 dark:text-white">Public images</h3>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="space-y-3">
                            <x-ui.input label="Card Image URL / Storage Path" name="featured_image"
                                wire:model.defer="featured_image" />
                            <label class="block">
                                <span class="form-label mb-2 block">Upload Card Image</span>
                                <input type="file" wire:model="featured_image_upload" accept="image/jpeg,image/png,image/webp,image/avif"
                                    class="form-input w-full">
                            </label>
                            @error('featured_image_upload')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                            @if ($featured_image_upload || $featured_image)
                                <img src="{{ $featured_image_upload ? $featured_image_upload->temporaryUrl() : \App\Support\WebsiteContent::assetPath($featured_image) }}"
                                    alt="Branch card preview" class="aspect-[4/3] w-full rounded-lg object-cover">
                            @endif
                        </div>
                        <div class="space-y-3">
                            <x-ui.input label="Hero Image URL / Storage Path" name="hero_image"
                                wire:model.defer="hero_image" />
                            <label class="block">
                                <span class="form-label mb-2 block">Upload Hero Image</span>
                                <input type="file" wire:model="hero_image_upload" accept="image/jpeg,image/png,image/webp,image/avif"
                                    class="form-input w-full">
                            </label>
                            @error('hero_image_upload')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                            @if ($hero_image_upload || $hero_image)
                                <img src="{{ $hero_image_upload ? $hero_image_upload->temporaryUrl() : \App\Support\WebsiteContent::assetPath($hero_image) }}"
                                    alt="Branch hero preview" class="aspect-[16/9] w-full rounded-lg object-cover">
                            @endif
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-200 pt-6 dark:border-white/10">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <x-ui.input label="Latitude" name="latitude" wire:model.defer="latitude" />
                            <button type="button"
                                class="mt-2 inline-flex items-center gap-1.5 rounded-lg px-1 text-xs font-semibold text-primary transition hover:text-primary/80 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                x-data
                                @click="navigator.geolocation.getCurrentPosition((pos) => { $wire.set('latitude', pos.coords.latitude); $wire.set('longitude', pos.coords.longitude); }, (err) => alert('Unable to get location: ' + err.message))">
                                <x-heroicon-o-map-pin class="h-4 w-4" />
                                Use current location
                            </button>
                        </div>
                        <x-ui.input label="Longitude" name="longitude" wire:model.defer="longitude" />
                    </div>
                    <div class="mt-4">
                        <x-ui.checkbox label="Active Branch" name="is_active" wire:model="is_active" />
                    </div>
                </section>

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
