<div>
    <form wire:submit="save" class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-950 dark:text-white">Website Settings</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Brand identity, guest contact channels and search appearance.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('website.home') }}" target="_blank" rel="noopener"
                    class="btn btn-secondary inline-flex items-center gap-2">
                    <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                    View Website
                </a>
                <x-ui.button type="submit" target="save" loadingText="Saving..." icon="check">Save Settings</x-ui.button>
            </div>
        </div>

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
            <div class="panel space-y-7">
                <section>
                    <div class="mb-4 flex items-center gap-2">
                        <x-heroicon-o-building-storefront class="h-5 w-5 text-primary" />
                        <h2 class="font-semibold text-slate-950 dark:text-white">Brand and contact</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-ui.input label="Business Name" name="business_name" wire:model.defer="business_name"
                            autocomplete="organization" />
                        <x-ui.input label="Tagline" name="tagline" wire:model.defer="tagline" />
                        <x-ui.input label="Email" name="email" wire:model.defer="email" type="email"
                            autocomplete="email" />
                        <x-ui.input label="Phone" name="phone" wire:model.defer="phone" autocomplete="tel" />
                        <x-ui.input label="WhatsApp" name="whatsapp" wire:model.defer="whatsapp" autocomplete="tel" />
                        <x-ui.input label="Google Maps Embed URL" name="google_map_url"
                            wire:model.defer="google_map_url" type="url" />
                        <div class="md:col-span-2">
                            <x-ui.textarea label="Address" name="address" wire:model.defer="address" rows="3" />
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-200 pt-6 dark:border-white/10">
                    <div class="mb-4 flex items-center gap-2">
                        <x-heroicon-o-share class="h-5 w-5 text-primary" />
                        <h2 class="font-semibold text-slate-950 dark:text-white">Social profiles</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-ui.input label="Facebook URL" name="facebook_url" wire:model.defer="facebook_url" type="url" />
                        <x-ui.input label="Instagram URL" name="instagram_url" wire:model.defer="instagram_url" type="url" />
                        <x-ui.input label="YouTube URL" name="youtube_url" wire:model.defer="youtube_url" type="url" />
                        <x-ui.input label="TikTok URL" name="tiktok_url" wire:model.defer="tiktok_url" type="url" />
                        <x-ui.input label="X / Twitter URL" name="x_url" wire:model.defer="x_url" type="url" />
                    </div>
                </section>

                <section class="border-t border-slate-200 pt-6 dark:border-white/10">
                    <div class="mb-4 flex items-center gap-2">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-primary" />
                        <h2 class="font-semibold text-slate-950 dark:text-white">Search appearance</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-ui.input label="Default Meta Title" name="meta_title" wire:model.defer="meta_title"
                            maxlength="70" />
                        <x-ui.textarea label="Default Meta Description" name="meta_description"
                            wire:model.defer="meta_description" rows="3" maxlength="180" />
                    </div>
                </section>
            </div>

            <aside class="panel space-y-6">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-photo class="h-5 w-5 text-primary" />
                    <h2 class="font-semibold text-slate-950 dark:text-white">Brand assets</h2>
                </div>

                <div class="space-y-5">
                    <label class="block">
                        <span class="form-label mb-2 block">Logo</span>
                        <input type="file" wire:model="logo" accept="image/jpeg,image/png,image/webp,image/avif"
                            class="form-input w-full">
                        @error('logo')<span class="mt-1 block text-sm text-danger">{{ $message }}</span>@enderror
                    </label>
                    @if ($logo || $logo_path)
                        <div class="grid h-28 place-items-center rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                            <img src="{{ $logo ? $logo->temporaryUrl() : \App\Support\WebsiteContent::assetPath($logo_path) }}"
                                alt="Logo preview" class="max-h-20 max-w-full object-contain">
                        </div>
                    @endif

                    <label class="block border-t border-slate-200 pt-5 dark:border-white/10">
                        <span class="form-label mb-2 block">Favicon</span>
                        <input type="file" wire:model="favicon" accept="image/png"
                            class="form-input w-full">
                        @error('favicon')<span class="mt-1 block text-sm text-danger">{{ $message }}</span>@enderror
                    </label>
                    @if ($favicon || $favicon_path)
                        <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 dark:border-white/10">
                            <img src="{{ $favicon ? $favicon->temporaryUrl() : \App\Support\WebsiteContent::assetPath($favicon_path) }}"
                                alt="Favicon preview" class="h-10 w-10 object-contain">
                            <span class="text-sm text-slate-500 dark:text-slate-400">Browser icon</span>
                        </div>
                    @endif

                    <label class="block border-t border-slate-200 pt-5 dark:border-white/10">
                        <span class="form-label mb-2 block">Homepage Hero</span>
                        <input type="file" wire:model="hero_background" accept="image/jpeg,image/png,image/webp,image/avif"
                            class="form-input w-full">
                        @error('hero_background')<span class="mt-1 block text-sm text-danger">{{ $message }}</span>@enderror
                    </label>
                    @if ($hero_background || $hero_background_path)
                        <img src="{{ $hero_background ? $hero_background->temporaryUrl() : \App\Support\WebsiteContent::assetPath($hero_background_path) }}"
                            alt="Homepage hero preview" class="aspect-[16/9] w-full rounded-lg object-cover">
                    @endif
                </div>
            </aside>
        </div>

        <details class="panel group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-slate-950 dark:text-white">
                <span class="flex items-center gap-2">
                    <x-heroicon-o-code-bracket-square class="h-5 w-5 text-primary" />
                    Advanced Content Overrides
                </span>
                <x-heroicon-o-chevron-down class="h-5 w-5 transition group-open:rotate-180" />
            </summary>
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-white/10">
                <x-ui.textarea label="Content JSON" name="content_json" wire:model.defer="content_json" rows="12"
                    class="font-mono text-xs" />
            </div>
        </details>

        <div class="sticky bottom-4 z-20 flex justify-end rounded-lg border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur dark:border-[#25314a] dark:bg-[#0e1726]/95">
            <x-ui.button type="submit" target="save" loadingText="Saving..." icon="check">Save Settings</x-ui.button>
        </div>
    </form>
</div>
