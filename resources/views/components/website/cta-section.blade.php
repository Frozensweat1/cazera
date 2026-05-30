@props(['title', 'subtitle' => null, 'image' => null])

<section {{ $attributes->merge(['class' => 'luxury-container py-16']) }}>
    <div class="relative overflow-hidden rounded-[2rem] border border-ivory/10 bg-charcoal p-8 md:p-12">
        @if ($image)
            <img src="{{ $image }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-28">
            <span class="absolute inset-0 bg-gradient-to-r from-ink via-ink/86 to-ink/45"></span>
        @endif
        <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
            <div class="max-w-3xl">
                <p class="eyebrow">{{ \App\Support\WebsiteContent::copy('global_cta.eyebrow', 'Plan your visit') }}</p>
                <h2 class="mt-3 font-serif text-4xl font-semibold leading-tight text-ivory md:text-6xl">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="mt-4 text-lg leading-8 text-parchment/76">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                {{ $slot }}
            </div>
        </div>
    </div>
</section>
