<div>
    <x-website.breadcrumbs :items="[['label' => 'Menu']]" />

    <section class="luxury-container pb-16 pt-10">
        <div class="grid gap-8 lg:grid-cols-[1fr_0.7fr] lg:items-end">
            <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('menu.eyebrow', $pageData['eyebrow'] ?? 'Menu & Services')" :title="\App\Support\WebsiteContent::copy('menu.title', $pageData['title'] ?? 'Discover our full menu.')" :subtitle="\App\Support\WebsiteContent::copy('menu.subtitle', $pageData['subtitle'] ?? 'Browse signature plates, snacks and drinks across all branches and categories.')" />

            <div class="flex items-center gap-3">
                <label class="glass-panel block rounded-full p-2 flex-1">
                    <span class="sr-only">Search menu items</span>
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search dishes, categories or branches" class="w-full rounded-full border border-ivory/10 bg-ink/60 px-5 py-4 text-sm text-ivory outline-none placeholder:text-parchment/45 focus:border-gold/60">
                </label>

                <div class="glass-panel rounded-full p-2">
                    <label class="sr-only">Filter by module</label>
                    <select wire:model.live="module" class="rounded-full bg-ink/60 border border-ivory/10 px-4 py-3 text-sm text-ivory outline-none focus:border-gold/60">
                        <option value="">All modules</option>
                        @foreach ($modules as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-10">
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 stagger">
                @forelse ($menuItems as $item)
                    <x-website.menu-card :item="$item" />
                @empty
                    <div class="rounded-3xl border border-ivory/10 p-8 text-parchment/70">No matching menu items found.</div>
                @endforelse
            </div>

            <div class="mt-8">
                @if ($menuItems->lastPage() > 1)
                    {{-- Compact pager for small screens --}}
                    <div class="flex items-center justify-center gap-2 sm:hidden" role="navigation" aria-label="Pagination Navigation Small">
                        <button wire:click="$set('page', {{ max(1, $menuItems->currentPage() - 1) }})" @disabled($menuItems->onFirstPage()) aria-label="Previous page" class="rounded-full px-3 py-2 text-sm font-semibold bg-ivory/6 text-ivory/80 disabled:opacity-40 focus:outline-none focus:ring-2 focus:ring-gold/70">&laquo;</button>
                        <span class="px-3 py-2 text-sm font-semibold text-parchment/70">Page {{ $menuItems->currentPage() }} of {{ $menuItems->lastPage() }}</span>
                        <button wire:click="$set('page', {{ min($menuItems->lastPage(), $menuItems->currentPage() + 1) }})" @disabled($menuItems->currentPage() === $menuItems->lastPage()) aria-label="Next page" class="rounded-full px-3 py-2 text-sm font-semibold bg-ivory/6 text-ivory/80 disabled:opacity-40 focus:outline-none focus:ring-2 focus:ring-gold/70">&raquo;</button>
                    </div>

                    {{-- Full pager for md+ screens --}}
                    <nav class="hidden sm:flex items-center justify-center gap-2" role="navigation" aria-label="Pagination Navigation">
                        <button wire:click="$set('page', {{ max(1, $menuItems->currentPage() - 1) }})" @disabled($menuItems->onFirstPage()) aria-label="Previous page" class="rounded-full px-3 py-2 text-sm font-semibold bg-ivory/6 text-ivory/80 disabled:opacity-40 focus:outline-none focus:ring-2 focus:ring-gold/70">&laquo;</button>

                        @php
                            $last = $menuItems->lastPage();
                            $current = $menuItems->currentPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if ($start > 1)
                            <button wire:click="$set('page', 1)" aria-label="Page 1" class="rounded-full px-3 py-2 text-sm font-semibold bg-ivory/6 text-ivory/80 focus:outline-none focus:ring-2 focus:ring-gold/70">1</button>
                            @if ($start > 2)
                                <span class="px-2 text-parchment/60">…</span>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            <button wire:click="$set('page', {{ $i }})" @if($menuItems->currentPage() === $i) aria-current="page" @endif aria-label="Page {{ $i }}" class="rounded-full px-3 py-2 text-sm font-semibold {{ $menuItems->currentPage() === $i ? 'bg-gold text-ink' : 'bg-ivory/6 text-ivory/80' }} focus:outline-none focus:ring-2 focus:ring-gold/70">{{ $i }}</button>
                        @endfor

                        @if ($end < $last)
                            @if ($end < $last - 1)
                                <span class="px-2 text-parchment/60">…</span>
                            @endif
                            <button wire:click="$set('page', {{ $last }})" aria-label="Page {{ $last }}" class="rounded-full px-3 py-2 text-sm font-semibold bg-ivory/6 text-ivory/80 focus:outline-none focus:ring-2 focus:ring-gold/70">{{ $last }}</button>
                        @endif

                        <button wire:click="$set('page', {{ min($menuItems->lastPage(), $menuItems->currentPage() + 1) }})" @disabled($menuItems->currentPage() === $menuItems->lastPage()) aria-label="Next page" class="rounded-full px-3 py-2 text-sm font-semibold bg-ivory/6 text-ivory/80 disabled:opacity-40 focus:outline-none focus:ring-2 focus:ring-gold/70">&raquo;</button>
                    </nav>
                @endif
            </div>
        </div>
    </section>
</div>
