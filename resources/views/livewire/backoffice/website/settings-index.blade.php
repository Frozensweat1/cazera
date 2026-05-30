<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Website System Settings</h1>
                <p class="text-gray-500">Configure website branding, contact details, social links, and hero visuals.</p>
            </div>
            <div class="flex w-full justify-end md:w-auto">
                <x-ui.button wire:click="save" icon="check">Save Settings</x-ui.button>
            </div>
        </div>

        <div class="panel grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
            <div class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.input label="Business Name" name="business_name" wire:model.defer="business_name" />
                    <x-ui.input label="Tagline" name="tagline" wire:model.defer="tagline" />
                    <x-ui.input label="Email" name="email" wire:model.defer="email" type="email" />
                    <x-ui.input label="Phone" name="phone" wire:model.defer="phone" />
                    <x-ui.input label="WhatsApp" name="whatsapp" wire:model.defer="whatsapp" />
                    <x-ui.input label="Google Maps URL" name="google_map_url" wire:model.defer="google_map_url" />
                </div>

                <x-ui.textarea label="Address" name="address" wire:model.defer="address" rows="4" />

                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.input label="Facebook URL" name="facebook_url" wire:model.defer="facebook_url" />
                    <x-ui.input label="Instagram URL" name="instagram_url" wire:model.defer="instagram_url" />
                    <x-ui.input label="YouTube URL" name="youtube_url" wire:model.defer="youtube_url" />
                    <x-ui.input label="TikTok URL" name="tiktok_url" wire:model.defer="tiktok_url" />
                    <x-ui.input label="X / Twitter URL" name="x_url" wire:model.defer="x_url" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.input label="Meta Title" name="meta_title" wire:model.defer="meta_title" />
                    <x-ui.textarea label="Meta Description" name="meta_description" wire:model.defer="meta_description"
                        rows="3" />
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h2 class="text-lg font-semibold text-slate-900">Frontend Content Overrides</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Optional JSON for public website copy. Example keys: homepage.hero_title,
                        homepage.hero_subtitle, footer.description, mobile_cta.title, contact.subtitle.
                    </p>
                    <div class="mt-4">
                        <x-ui.textarea label="Content JSON" name="content_json" wire:model.defer="content_json"
                            rows="10" />
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-3xl bg-slate-50 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">Visual Assets</h2>
                    <p class="mt-2 text-sm text-slate-600">Upload your website logo, favicon, and hero background image.
                    </p>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Logo</label>
                            <input type="file" wire:model="logo"
                                class="mt-2 w-full rounded-3xl border border-slate-200 bg-white px-4 py-3" />
                            @error('logo')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Favicon</label>
                            <input type="file" wire:model="favicon"
                                class="mt-2 w-full rounded-3xl border border-slate-200 bg-white px-4 py-3" />
                            @error('favicon')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Hero Background Image</label>
                            <input type="file" wire:model="hero_background"
                                class="mt-2 w-full rounded-3xl border border-slate-200 bg-white px-4 py-3" />
                            @error('hero_background')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Current Files</h2>
                    <div class="mt-4 space-y-4 text-sm text-slate-600">
                        @if ($logo_path)
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('storage/' . $logo_path) }}" alt="Logo preview"
                                    class="h-12 w-12 rounded-xl object-contain" />
                                <span>Logo uploaded</span>
                            </div>
                        @endif
                        @if ($favicon_path)
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('storage/' . $favicon_path) }}" alt="Favicon preview"
                                    class="h-10 w-10 rounded-sm object-contain" />
                                <span>Favicon uploaded</span>
                            </div>
                        @endif
                        @if ($hero_background_path)
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('storage/' . $hero_background_path) }}" alt="Hero preview"
                                    class="h-16 w-24 rounded-2xl object-cover" />
                                <span>Hero background uploaded</span>
                            </div>
                        @endif
                        @if (!$logo_path && !$favicon_path && !$hero_background_path)
                            <p class="text-slate-500">No website assets uploaded yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-[#25314a] dark:bg-[#0e1726]">
            <x-ui.button wire:click="save" icon="check">Save Settings</x-ui.button>
        </div>
    </div>
</div>
