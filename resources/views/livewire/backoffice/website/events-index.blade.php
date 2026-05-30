<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Website Events</h1>
                <p class="text-gray-500">Manage promotions, campaigns, happy hour specials, and live events.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Event</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5 grid gap-4 md:grid-cols-4">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search events..." />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach
                </x-ui.select>
                <x-ui.select name="filterStatus" wire:model.live="filterStatus">
                    <option value="">Any status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead><tr><th>Event</th><th>Branch</th><th>Date</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td><div class="font-semibold">{{ $event->title }}</div><div class="text-xs text-gray-500">{{ $event->tag }}</div></td>
                            <td>{{ $event->branch?->name ?? 'All branches' }}</td>
                            <td>{{ $event->date_label ?: $event->starts_at?->format('Y-m-d H:i') ?: '-' }}</td>
                            <td><span class="badge {{ $event->is_published ? 'bg-success' : 'bg-warning' }}">{{ $event->is_published ? 'Published' : 'Draft' }}</span></td>
                            <td class="text-center">
                                <div class="flex justify-center gap-2">
                                    <x-ui.button size="sm" icon="pencil-square" wire:click="edit({{ $event->id }})">Edit</x-ui.button>
                                    <x-ui.button size="sm" variant="secondary" icon="eye" wire:click="togglePublished({{ $event->id }})">{{ $event->is_published ? 'Unpublish' : 'Publish' }}</x-ui.button>
                                    <x-ui.button size="sm" variant="danger" icon="trash" wire:click="delete({{ $event->id }})">Delete</x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-gray-500">No events found.</td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
            <div class="mt-5">{{ $events->links() }}</div>
        </div>

        <x-ui.modal name="website-event-form" maxWidth="4xl">
            <x-slot:title>{{ $eventId ? 'Edit Event' : 'Create Event' }}</x-slot:title>
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.select label="Branch" name="branch_id" wire:model="branch_id"><option value="">All branches</option>@foreach ($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</x-ui.select>
                <x-ui.input label="Title" name="title" wire:model.live="title" />
                <x-ui.input label="Slug" name="slug" wire:model.live="slug" />
                <x-ui.input label="Tag" name="tag" wire:model.live="tag" />
                <x-ui.input label="Date Label" name="date_label" wire:model.live="date_label" />
                <x-ui.input label="Image URL / Storage Path" name="image" wire:model.live="image" />
                <x-ui.input label="Starts At" type="datetime-local" name="starts_at" wire:model.live="starts_at" />
                <x-ui.input label="Ends At" type="datetime-local" name="ends_at" wire:model.live="ends_at" />
                <div class="md:col-span-2"><x-ui.textarea label="Description" name="description" wire:model.live="description" /></div>
                <div class="md:col-span-2"><x-ui.textarea label="Body" name="body" wire:model.live="body" /></div>
                <x-ui.checkbox label="Featured" name="is_featured" wire:model="is_featured" />
                <x-ui.checkbox label="Published" name="is_published" wire:model="is_published" />
                <x-ui.input label="Sort Order" type="number" name="sort_order" wire:model.live="sort_order" />
            </div>
            <x-slot:footer><div class="flex justify-end gap-3"><x-ui.button type="button" variant="outline-secondary" x-on:click="$dispatch('close-modal', 'website-event-form')">Cancel</x-ui.button><x-ui.button wire:click="save" icon="check">Save Event</x-ui.button></div></x-slot:footer>
        </x-ui.modal>
    </div>
</div>
