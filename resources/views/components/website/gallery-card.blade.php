@props(['item'])

<button type="button" @click="active = @js($item)" {{ $attributes->merge(['class' => 'group relative block w-full overflow-hidden rounded-[1.5rem] border border-ivory/10 text-left focus:outline-none focus:ring-2 focus:ring-gold/70']) }}>
    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy" class="h-full min-h-72 w-full object-cover transition duration-700 group-hover:scale-105">
    <span class="absolute inset-0 bg-gradient-to-t from-ink/88 via-ink/10 to-transparent"></span>
    <span class="absolute bottom-5 left-5 right-5">
        <span class="eyebrow">{{ $item['category'] }} @if(($item['type'] ?? 'image') === 'video') / Video @endif</span>
        <span class="mt-2 block font-serif text-2xl font-semibold text-ivory">{{ $item['title'] }}</span>
    </span>
</button>
