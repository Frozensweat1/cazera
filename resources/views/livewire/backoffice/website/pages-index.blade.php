<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Website Pages</h1>
                <p class="mt-1 text-sm text-gray-500">Published copy, page imagery and search metadata.</p>
            </div>
            <a href="{{ $publicUrl }}" target="_blank" rel="noopener"
                class="btn btn-secondary inline-flex items-center gap-2 self-start md:self-auto">
                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                View {{ $pages[$slug] ?? 'Page' }}
            </a>
        </div>
        <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
            <div class="panel">
                <h2 class="mb-4 font-semibold">Pages</h2>
                <div class="space-y-2">
                    @foreach ($pages as $pageSlug => $label)
                        <button type="button" wire:click="selectPage('{{ $pageSlug }}')"
                            class="flex w-full items-center justify-between rounded-lg px-4 py-3 text-left text-sm font-semibold transition {{ $slug === $pageSlug ? 'bg-primary text-white' : 'bg-slate-50 hover:bg-slate-100 dark:bg-white/5 dark:hover:bg-white/10' }}">
                            <span>{{ $label }}</span>
                            @if ($slug === $pageSlug)
                                <x-heroicon-o-chevron-right class="h-4 w-4" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="panel">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4 dark:border-white/10">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Editing</p>
                        <h2 class="text-lg font-semibold">{{ $pages[$slug] ?? str($slug)->headline() }}</h2>
                    </div>
                    <x-ui.checkbox label="Published" name="is_published" wire:model="is_published" />
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.input label="Eyebrow" name="eyebrow" wire:model.defer="eyebrow" />
                    <x-ui.input label="Title" name="title" wire:model.defer="title" />
                    <div>
                        <x-ui.input label="Hero Image URL / Storage Path" name="hero_image" wire:model.defer="hero_image" />
                    </div>
                    <div class="md:col-span-2">
                        <label for="hero_image_upload" class="form-label mb-2">Upload Hero Image</label>
                        <input id="hero_image_upload" type="file" wire:model="hero_image_upload" accept="image/*"
                            class="form-input w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-[#0e1726]">
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
                    <div class="md:col-span-2"><x-ui.textarea label="Subtitle" name="subtitle" wire:model.defer="subtitle" rows="3" /></div>
                    <div class="md:col-span-2"><x-ui.textarea label="Body / Story" name="body" wire:model.defer="body" rows="7" /></div>
                    <x-ui.input label="Meta Title" name="meta_title" wire:model.defer="meta_title" maxlength="70" />
                    <x-ui.textarea label="Meta Description" name="meta_description" wire:model.defer="meta_description" rows="3" maxlength="180" />
                    <details class="md:col-span-2 rounded-lg border border-slate-200 p-4 dark:border-white/10">
                        <summary class="cursor-pointer font-semibold">Advanced Section Content</summary>
                        <div class="mt-4">
                            <x-ui.textarea label="Sections JSON" name="sections_json" wire:model.defer="sections_json"
                                rows="12" class="font-mono text-xs" />
                        </div>
                    </details>
                </div>
                <div class="mt-5 flex justify-end"><x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save Page Content</x-ui.button></div>
            </div>
        </div>
    </div>
</div>
