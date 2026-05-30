@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'luxury-container pt-32 text-sm text-parchment/68']) }} aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-2">
        <li><a href="{{ route('website.home') }}" class="hover:text-gold">Home</a></li>
        @foreach ($items as $item)
            <li aria-hidden="true">/</li>
            <li>
                @if ($loop->last || empty($item['url']))
                    <span class="text-ivory">{{ $item['label'] }}</span>
                @else
                    <a href="{{ $item['url'] }}" class="hover:text-gold">{{ $item['label'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
