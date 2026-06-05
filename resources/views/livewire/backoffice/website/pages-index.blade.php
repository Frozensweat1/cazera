<div>
    <div class="space-y-6">
        <div><h1 class="text-2xl font-bold">Website Page Content</h1><p class="text-gray-500">Manage page copy for homepage, about, mission, vision, contact, reviews, events, gallery, branches and careers.</p></div>
        <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
            <div class="panel">
                <h2 class="mb-4 font-semibold">Pages</h2>
                <div class="space-y-2">
                    @foreach ($pages as $pageSlug => $label)
                        <button type="button" wire:click="$set('slug', '{{ $pageSlug }}')" class="block w-full rounded-lg px-4 py-3 text-left text-sm font-semibold transition {{ $slug === $pageSlug ? 'bg-primary text-white' : 'bg-slate-50 hover:bg-slate-100 dark:bg-white/5' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="panel">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.input label="Slug" name="slug" wire:model.live="slug" />
                    <x-ui.input label="Eyebrow" name="eyebrow" wire:model.live="eyebrow" />
                    <x-ui.input label="Title" name="title" wire:model.live="title" />
                    <div>
                        <x-ui.input label="Hero Image URL / Storage Path" name="hero_image" wire:model.live="hero_image" />
                        <p class="mt-1 text-xs text-gray-500">Paste an external image URL or an existing storage path.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="hero_image_upload" class="form-label mb-2">Upload Hero Image</label>
                        <input id="hero_image_upload" type="file" wire:model="hero_image_upload" accept="image/*"
                            class="form-input w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-[#0e1726]">
                        <p class="mt-1 text-xs text-gray-500">Uploading a file will replace the URL/storage path when saved.</p>
                        @error('hero_image_upload')
                            <div class="mt-1 text-xs font-semibold text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($hero_image_upload || $hero_image)
                        <div class="md:col-span-2">
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-white/5">
                                @if ($hero_image_upload)
                                    <img src="{{ $hero_image_upload->temporaryUrl() }}" alt="Hero image preview"
                                        class="h-56 w-full object-cover">
                                @else
                                    <img src="{{ \App\Support\WebsiteContent::assetPath($hero_image) ?? $hero_image }}" alt="Hero image preview"
                                        class="h-56 w-full object-cover">
                                @endif
                            </div>
                        </div>
                    @endif
                    <div class="md:col-span-2"><x-ui.textarea label="Subtitle" name="subtitle" wire:model.live="subtitle" /></div>
                    <div class="md:col-span-2"><x-ui.textarea label="Body / Story / Mission / Vision" name="body" wire:model.live="body" /></div>
                    <x-ui.input label="Meta Title" name="meta_title" wire:model.live="meta_title" />
                    <x-ui.input label="Meta Description" name="meta_description" wire:model.live="meta_description" />
                    <x-ui.checkbox label="Published" name="is_published" wire:model="is_published" />
                </div>
                <div class="mt-5 flex justify-end"><x-ui.button wire:click="save" icon="check">Save Page Content</x-ui.button></div>
            </div>
        </div>
    </div>
</div>
