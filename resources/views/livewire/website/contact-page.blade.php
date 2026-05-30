<div>
    <x-website.breadcrumbs :items="[['label' => 'Contact']]" />

    <section class="luxury-container pb-20 pt-10">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr]">
            <div>
                <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('contact.eyebrow', $page['eyebrow'] ?? 'Contact')" :title="\App\Support\WebsiteContent::copy('contact.title', $page['title'] ?? 'Call, message or ask the right branch.')" :subtitle="\App\Support\WebsiteContent::copy('contact.subtitle', $page['subtitle'] ?? 'Use the form for general inquiries, private events, menu questions, media requests or feedback. Direct CTAs stay visible for faster conversion.')" />
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($branches as $branch)
                        <article class="rounded-3xl border border-ivory/10 p-5">
                            <h3 class="font-serif text-2xl font-semibold text-ivory">{{ $branch['name'] }}</h3>
                            <p class="mt-1 text-sm text-parchment/70">{{ $branch['location'] }}</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($branch['phone'])
                                    <x-website.button href="tel:{{ $branch['phone'] }}" class="px-4 py-2.5">Call</x-website.button>
                                @endif
                                @if ($branch['whatsapp'])
                                    <x-website.button href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $branch['whatsapp']) }}" target="_blank" variant="whatsapp" class="px-4 py-2.5">WhatsApp</x-website.button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[2rem] border border-ivory/10 bg-charcoal p-6 md:p-8">
                @if ($successMessage)
                    <div class="mb-6 rounded-3xl border border-emerald-400/30 bg-emerald-500/10 p-4 text-emerald-100">{{ $successMessage }}</div>
                @endif

                <form wire:submit.prevent="submit" class="grid gap-5">
                    <div class="hidden" aria-hidden="true">
                        <input type="text" wire:model.defer="company" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <label>
                            <span class="text-sm font-bold text-ivory">Name</span>
                            <input type="text" wire:model.defer="name" class="mt-2 w-full rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Your name">
                            @error('name')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        </label>
                        <label>
                            <span class="text-sm font-bold text-ivory">Email</span>
                            <input type="email" wire:model.defer="email" class="mt-2 w-full rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="you@example.com">
                            @error('email')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <label>
                            <span class="text-sm font-bold text-ivory">Phone</span>
                            <input type="text" wire:model.defer="phone" class="mt-2 w-full rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Optional phone">
                            @error('phone')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        </label>
                        <label>
                            <span class="text-sm font-bold text-ivory">Branch</span>
                            <select wire:model.defer="branch_id" class="mt-2 w-full rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70">
                                <option value="">Any branch</option>
                                @foreach ($branches as $branch)
                                    @if($branch['id'])
                                        <option value="{{ $branch['id'] }}">{{ $branch['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('branch_id')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <label>
                            <span class="text-sm font-bold text-ivory">Inquiry category</span>
                            <select wire:model.defer="inquiry_category" class="mt-2 w-full rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70">
                                @foreach (['General inquiry', 'Private event', 'Menu question', 'Branch contact', 'Media request', 'Feedback'] as $option)
                                    <option>{{ $option }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="text-sm font-bold text-ivory">Subject</span>
                            <input type="text" wire:model.defer="subject" class="mt-2 w-full rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Optional subject">
                            @error('subject')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <label>
                        <span class="text-sm font-bold text-ivory">Message</span>
                        <textarea wire:model.defer="message" rows="6" class="mt-2 w-full rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="How can the team help?"></textarea>
                        @error('message')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                    </label>
                    <x-website.button type="submit" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit">Send Message</span>
                        <span wire:loading wire:target="submit">Sending...</span>
                    </x-website.button>
                </form>
            </div>
        </div>
    </section>

    <section class="luxury-container pb-20">
        <div class="rounded-[2rem] border border-ivory/10 bg-charcoal p-7">
            <p class="eyebrow">Map</p>
            @if ($settings?->google_map_url)
                <iframe src="{{ $settings->google_map_url }}" title="{{ \App\Support\WebsiteContent::copy('brand.name', 'Cazera') }} map" loading="lazy" class="mt-5 h-96 w-full rounded-3xl border-0"></iframe>
            @else
                <div class="mt-5 grid h-80 place-items-center rounded-3xl bg-ivory/[0.05] p-8 text-center text-parchment/70">Add a Google Maps embed URL in website settings to show the map here.</div>
            @endif
        </div>
    </section>
</div>
