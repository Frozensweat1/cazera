<div>
    <x-website.breadcrumbs :items="[['label' => 'Careers']]" />

    <section class="luxury-container pb-20 pt-10">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-end">
            <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('careers.eyebrow', $page['eyebrow'] ?? 'Careers')" :title="\App\Support\WebsiteContent::copy('careers.title', $page['title'] ?? 'Join a team that treats hospitality as craft.')" :subtitle="\App\Support\WebsiteContent::copy('careers.subtitle', $page['subtitle'] ?? 'Bring your presence, discipline and warmth to a team that believes every guest interaction should feel considered.')" />
            <img src="{{ $page['hero_image'] ?? \App\Support\WebsiteContent::image('photo-1556761175-b413da4baf72', 1400) }}" alt="Hospitality team preparing service" class="rounded-[2rem] object-cover">
        </div>
    </section>

    <section class="bg-ivory py-20 text-ink">
        <div class="luxury-container">
            <div class="grid gap-6 md:grid-cols-3">
                @foreach (['Guest-first service', 'Calm excellence', 'Growth across branches'] as $culture)
                    <article class="rounded-[1.5rem] bg-ink p-6 text-ivory">
                        <h2 class="font-serif text-3xl font-semibold">{{ $culture }}</h2>
                        <p class="mt-3 text-sm leading-7 text-parchment/72">We value detail, composure, warmth and a shared standard for memorable visits.</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="luxury-container py-20">
        <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr]">
            <div>
                <x-website.section-heading eyebrow="Vacancies" title="Open hospitality roles." />
                <div class="space-y-4">
                    @foreach ($vacancies as $vacancy)
                        <article class="rounded-3xl border border-ivory/10 p-5">
                            <h3 class="font-serif text-2xl font-semibold text-ivory">{{ $vacancy['role'] }}</h3>
                            <p class="mt-2 text-sm text-parchment/70">{{ $vacancy['location'] }} / {{ $vacancy['type'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[2rem] border border-ivory/10 bg-charcoal p-6 md:p-8">
                <h2 class="font-serif text-4xl font-semibold text-ivory">Application interest</h2>
                @if ($successMessage)
                    <div class="mt-5 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 p-4 text-emerald-100">{{ $successMessage }}</div>
                @endif
                <form wire:submit.prevent="apply" class="mt-6 grid gap-4">
                    <div class="hidden" aria-hidden="true">
                        <input wire:model.defer="company" type="text" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <input wire:model.defer="name" type="text" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Full name">
                        <input wire:model.defer="email" type="email" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Email address">
                    </div>
                    @error('name')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                    @error('email')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                    <div class="grid gap-4 md:grid-cols-2">
                        <input wire:model.defer="phone" type="text" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Phone optional">
                        <select wire:model.defer="role" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70">
                            <option value="">Choose a role</option>
                            @foreach ($vacancies as $vacancy)
                                <option value="{{ $vacancy['role'] }}">{{ $vacancy['role'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('role')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                    <textarea wire:model.defer="message" rows="6" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Tell us about your hospitality experience"></textarea>
                    @error('message')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                    <x-website.button type="submit" wire:loading.attr="disabled" wire:target="apply">
                        <span wire:loading.remove wire:target="apply">Submit Interest</span>
                        <span wire:loading wire:target="apply">Submitting...</span>
                    </x-website.button>
                </form>
            </div>
        </div>
    </section>
</div>
