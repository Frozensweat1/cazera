@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'left'])
<div {{ $attributes->merge(['class' => 'mb-8 ' . ($align === 'center' ? 'mx-auto max-w-3xl text-center' : 'max-w-3xl')]) }}>
    @if ($eyebrow)
        <p class="eyebrow">{{ $eyebrow }}</p>
    @endif
    <h2 class="mt-3 font-serif text-4xl font-semibold leading-tight text-ivory md:text-6xl">{{ $title }}</h2>
    @if($subtitle)
        <p class="mt-4 text-base leading-8 text-parchment/74 md:text-lg">{{ $subtitle }}</p>
    @endif
</div>
