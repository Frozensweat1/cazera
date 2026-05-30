<div>
    <x-website.breadcrumbs :items="[['label' => 'Gallery']]" />

    <section class="luxury-container pb-20 pt-10">
        <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('gallery.eyebrow', $page['eyebrow'] ?? 'Gallery')" :title="\App\Support\WebsiteContent::copy('gallery.title', $page['title'] ?? 'Ambiance, plates, events and rooms worth entering.')" :subtitle="\App\Support\WebsiteContent::copy('gallery.subtitle', $page['subtitle'] ?? 'Filter the gallery by mood. Images open in a lightweight preview for touch and keyboard-friendly browsing.')" />

        <div class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('website.gallery') }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $category === 'all' ? 'bg-gold text-ink' : 'border border-ivory/12 text-ivory hover:border-gold/60' }}">All</a>
            @foreach ($categories as $cat)
                <a href="{{ route('website.gallery', $cat) }}" class="rounded-full px-4 py-2 text-sm font-bold capitalize transition {{ $category === $cat ? 'bg-gold text-ink' : 'border border-ivory/12 text-ivory hover:border-gold/60' }}">{{ $cat }}</a>
            @endforeach
        </div>

        <div x-data="{ active: null }">
            <div class="columns-1 gap-5 sm:columns-2 lg:columns-3">
                @foreach ($items as $item)
                    <x-website.gallery-card :item="$item" class="mb-5 break-inside-avoid" />
                @endforeach
            </div>
            <template x-if="active">
                <div class="fixed inset-0 z-[70] grid place-items-center bg-ink/92 p-4" @click.self="active = null" @keydown.escape.window="active = null">
                    <button type="button" @click="active = null" class="absolute right-5 top-5 rounded-full bg-ivory px-4 py-2 text-sm font-bold text-ink">Close</button>
                    <div class="max-w-5xl">
                        <img :src="active.image" :alt="active.title" class="max-h-[78vh] rounded-2xl object-contain">
                        <p class="mt-4 text-center font-serif text-2xl text-ivory" x-text="active.title"></p>
                    </div>
                </div>
            </template>
        </div>
    </section>
</div>
