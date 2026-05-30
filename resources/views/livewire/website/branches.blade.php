<div>
    <x-website.breadcrumbs :items="[['label' => 'Branches']]" />

    <section class="luxury-container pb-16 pt-10">
        <div class="grid gap-8 lg:grid-cols-[1fr_0.7fr] lg:items-end">
            <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('branches.eyebrow', $page['eyebrow'] ?? 'Branches')" :title="\App\Support\WebsiteContent::copy('branches.title', $page['title'] ?? 'Choose the atmosphere that fits tonight.')" :subtitle="\App\Support\WebsiteContent::copy('branches.subtitle', $page['subtitle'] ?? 'Every branch card is built for quick decisions: see the mood, location, hours, then call, WhatsApp or open directions.')" />
            <label class="glass-panel block rounded-full p-2">
                <span class="sr-only">Search branches</span>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by branch, location or mood" class="w-full rounded-full border border-ivory/10 bg-ink/60 px-5 py-4 text-sm text-ivory outline-none placeholder:text-parchment/45 focus:border-gold/60">
            </label>
        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3 stagger">
            @forelse ($branches as $branch)
                <x-website.branch-card :branch="$branch" />
            @empty
                <div class="rounded-3xl border border-ivory/10 p-8 text-parchment/70">No matching branches found.</div>
            @endforelse
        </div>
    </section>
</div>
