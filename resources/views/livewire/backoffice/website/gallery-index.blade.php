<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Website Gallery</h1><p class="text-gray-500">Manage ambiance, food, events, nightlife, VIP, and interior media.</p></div>
            <x-ui.button icon="plus" wire:click="create">Add Media</x-ui.button>
        </div>
        <div class="panel">
            <div class="mb-5 grid gap-4 md:grid-cols-4">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search gallery..." />
                <x-ui.select name="filterCategory" wire:model.live="filterCategory"><option value="">All categories</option>@foreach ($categories as $cat)<option value="{{ $cat }}">{{ str($cat)->headline() }}</option>@endforeach</x-ui.select>
                <x-ui.select name="filterStatus" wire:model.live="filterStatus"><option value="">Any status</option><option value="published">Published</option><option value="draft">Draft</option></x-ui.select>
            </div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($items as $item)
                    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-[#0e1726]">
                        <div class="aspect-[16/10] bg-slate-100"><img src="{{ \App\Support\WebsiteContent::assetPath($item->image) ?: \App\Support\WebsiteContent::image('photo-1517248135467-4c7edcad34c4', 900) }}" class="h-full w-full object-cover" alt="{{ $item->title }}"></div>
                        <div class="space-y-3 p-4">
                            <div class="flex items-start justify-between gap-3"><div><h2 class="font-bold">{{ $item->title }}</h2><p class="text-sm text-gray-500">{{ str($item->category)->headline() }} / {{ str($item->type)->headline() }}</p></div><span class="badge {{ $item->is_published ? 'bg-success' : 'bg-warning' }}">{{ $item->is_published ? 'Live' : 'Draft' }}</span></div>
                            <div class="flex gap-2"><x-ui.button size="sm" icon="pencil-square" wire:click="edit({{ $item->id }})">Edit</x-ui.button><x-ui.button size="sm" variant="danger" icon="trash" wire:click="delete({{ $item->id }})">Delete</x-ui.button></div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-gray-500 md:col-span-2 xl:col-span-3">No gallery items found.</div>
                @endforelse
            </div>
            <div class="mt-5">{{ $items->links() }}</div>
        </div>

        <x-ui.modal name="gallery-form" maxWidth="3xl">
            <x-slot:title>{{ $galleryId ? 'Edit Media' : 'Create Media' }}</x-slot:title>
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.select label="Branch" name="branch_id" wire:model="branch_id"><option value="">All branches</option>@foreach ($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</x-ui.select>
                <x-ui.input label="Title" name="title" wire:model.live="title" />
                <x-ui.input label="Slug" name="slug" wire:model.live="slug" />
                <x-ui.select label="Category" name="category" wire:model="category">@foreach ($categories as $cat)<option value="{{ $cat }}">{{ str($cat)->headline() }}</option>@endforeach</x-ui.select>
                <x-ui.select label="Type" name="type" wire:model="type"><option value="image">Image</option><option value="video">Video</option></x-ui.select>
                <x-ui.input label="Image URL / Storage Path" name="image" wire:model.live="image" />
                <x-ui.input label="Video URL" name="video_url" wire:model.live="video_url" />
                <x-ui.input label="Sort Order" type="number" name="sort_order" wire:model.live="sort_order" />
                <div class="md:col-span-2"><x-ui.textarea label="Description" name="description" wire:model.live="description" /></div>
                <x-ui.checkbox label="Featured" name="is_featured" wire:model="is_featured" />
                <x-ui.checkbox label="Published" name="is_published" wire:model="is_published" />
            </div>
            <x-slot:footer><div class="flex justify-end gap-3"><x-ui.button type="button" variant="outline-secondary" x-on:click="$dispatch('close-modal', 'gallery-form')">Cancel</x-ui.button><x-ui.button wire:click="save" icon="check">Save Media</x-ui.button></div></x-slot:footer>
        </x-ui.modal>
    </div>
</div>
