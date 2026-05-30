@props(['href' => null, 'variant' => 'primary', 'target' => null])
@php
    $classes = [
        'primary' => 'bg-gold text-ink hover:bg-parchment',
        'secondary' => 'border border-ivory/18 bg-ivory/8 text-ivory hover:border-gold/50 hover:text-gold',
        'ghost' => 'text-ivory/80 hover:bg-ivory/8 hover:text-ivory',
        'whatsapp' => 'bg-emerald-600 text-white hover:bg-emerald-500',
    ][$variant] ?? 'bg-gold text-ink hover:bg-parchment';
    $base = 'inline-flex items-center justify-center gap-2 rounded-full px-5 py-3 text-sm font-extrabold transition focus:outline-none focus:ring-2 focus:ring-gold/70 focus:ring-offset-2 focus:ring-offset-ink';
@endphp

@if ($href)
    <a href="{{ $href }}" @if($target) target="{{ $target }}" rel="noopener" @endif {{ $attributes->merge(['class' => "{$base} {$classes}"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" {{ $attributes->except('type')->merge(['class' => "{$base} {$classes}"]) }}>
        {{ $slot }}
    </button>
@endif
