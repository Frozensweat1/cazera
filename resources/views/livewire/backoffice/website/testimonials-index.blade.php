<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Website Testimonials</h1>
                <p class="text-gray-500">Manage published testimonials displayed on the public website.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Testimonial</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid gap-4 md:grid-cols-4 mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search testimonials..." />
                <x-ui.select label="Branch" name="filterBranch" wire:model="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Module" name="filterModule" wire:model="filterModule">
                    <option value="">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Published" name="filterStatus" wire:model="filterStatus">
                    <option value="">Any</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Author</th>
                        <th>Quote</th>
                        <th>Rating</th>
                        <th>Published</th>
                        <th>Featured</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($testimonials as $testimonial)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $testimonial->author_name }}</div>
                                <div class="text-xs text-gray-500">{{ $testimonial->title ?? $testimonial->company }}
                                </div>
                            </td>
                            <td class="max-w-md truncate">{{ \Illuminate\Support\Str::limit($testimonial->quote, 80) }}
                            </td>
                            <td>{{ $testimonial->rating }}/5</td>
                            <td>
                                @if ($testimonial->is_published)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-warning">No</span>
                                @endif
                            </td>
                            <td>
                                @if ($testimonial->is_featured)
                                    <span class="badge bg-primary">Featured</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.button size="sm" variant="{{ $testimonial->is_published ? 'secondary' : 'success' }}" icon="{{ $testimonial->is_published ? 'eye-slash' : 'check-circle' }}"
                                    wire:click="togglePublished({{ $testimonial->id }})">{{ $testimonial->is_published ? 'Hide' : 'Publish' }}</x-ui.button>
                                <x-ui.button size="sm" variant="{{ $testimonial->is_featured ? 'secondary' : 'primary' }}" icon="star"
                                    wire:click="toggleFeatured({{ $testimonial->id }})">{{ $testimonial->is_featured ? 'Unfeature' : 'Feature' }}</x-ui.button>
                                <x-ui.button size="sm" icon="pencil-square"
                                    wire:click="edit({{ $testimonial->id }})">Edit</x-ui.button>
                                <x-ui.button size="sm" variant="danger" icon="trash"
                                    wire:click="confirmDelete({{ $testimonial->id }})">Delete</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">No testimonials found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $testimonials->links() }}</div>
        </div>

        <x-ui.modal name="testimonial-form" maxWidth="2xl">
            <x-slot:title>{{ $testimonialId ? 'Edit Testimonial' : 'Create Testimonial' }}</x-slot:title>
            <div class="space-y-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.select label="Branch" name="branch_id" wire:model="branch_id">
                        <option value="">None</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Module" name="module_id" wire:model="module_id">
                        <option value="">None</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input label="Author Name" name="author_name" wire:model.live="author_name" />
                    <x-ui.input label="Title / Role" name="title" wire:model.live="title" />
                    <x-ui.input label="Company" name="company" wire:model.live="company" />
                    <x-ui.input label="Rating" name="rating" wire:model.live="rating" type="number" min="1"
                        max="5" />
                </div>

                <x-ui.textarea label="Quote" name="quote" wire:model.live="quote" />
                <div class="grid gap-4 md:grid-cols-3">
                    <x-ui.checkbox label="Published" name="is_published" wire:model="is_published" />
                    <x-ui.checkbox label="Featured" name="is_featured" wire:model="is_featured" />
                    <x-ui.input label="Sort Order" name="sort_order" wire:model.live="sort_order" type="number"
                        min="0" />
                </div>

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'testimonial-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Testimonial</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>

        @foreach ($testimonials as $testimonial)
            <x-ui.modal name="delete-testimonial-{{ $testimonial->id }}" maxWidth="sm">
                <x-slot:title>Delete Testimonial</x-slot:title>
                <p>Are you sure you want to delete the testimonial from
                    <strong>{{ $testimonial->author_name }}</strong>?
                </p>
                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-secondary"
                            x-on:click="$dispatch('close-modal', 'delete-testimonial-{{ $testimonial->id }}')">Cancel</x-ui.button>
                        <x-ui.button wire:click="delete({{ $testimonial->id }})"
                            variant="danger">Delete</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        @endforeach
    </div>
</div>
