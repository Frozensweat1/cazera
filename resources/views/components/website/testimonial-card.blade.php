@props(['testimonial'])

<article {{ $attributes->merge(['class' => 'rounded-[1.5rem] border border-ivory/10 bg-ivory/[0.045] p-6']) }}>
    <div class="flex gap-1 text-gold" aria-label="{{ $testimonial['rating'] ?? 5 }} out of 5 stars">
        @for ($i = 1; $i <= 5; $i++)
            <span aria-hidden="true">{{ $i <= ($testimonial['rating'] ?? 5) ? '★' : '☆' }}</span>
        @endfor
    </div>
    <blockquote class="mt-5 font-serif text-2xl leading-9 text-ivory">“{{ $testimonial['quote'] }}”</blockquote>
    <div class="mt-6 border-t border-ivory/10 pt-5">
        <p class="font-bold text-ivory">{{ $testimonial['name'] }}</p>
        @if ($testimonial['title'])
            <p class="text-sm text-parchment/68">{{ $testimonial['title'] }}</p>
        @endif
    </div>
</article>
