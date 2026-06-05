@php
    $settings = \App\Support\WebsiteContent::settings();
    $title = $title ?? ($settings?->meta_title ?? ($settings?->business_name ?? config('app.name', 'Cazera')));
    $description = $description ?? ($settings?->meta_description ?? 'A cinematic hospitality showcase for branches, menus, galleries, reviews, events and direct contact.');
    $image = $image ?? (\App\Support\WebsiteContent::assetPath($settings?->hero_background) ?: 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=82');
    $businessName = $settings?->business_name ?: config('app.name', 'Cazera');
    $favicon = \App\Support\WebsiteContent::assetPath($settings?->favicon);
    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Restaurant',
        'name' => $businessName,
        'description' => $description,
        'url' => url('/'),
        'image' => $image,
        'telephone' => $settings?->phone,
        'email' => $settings?->email,
        'address' => $settings?->address,
        'sameAs' => array_values(array_filter([
            $settings?->facebook_url,
            $settings?->instagram_url,
            $settings?->youtube_url,
            $settings?->tiktok_url,
            $settings?->x_url,
        ])),
    ]);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0f0d0a">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:site_name" content="{{ $businessName }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $image }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($favicon)
        <link rel="icon" type="image/png" href="{{ $favicon }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
</head>

<body class="site-shell min-h-screen antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[80] focus:bg-gold focus:px-4 focus:py-2 focus:text-ink">Skip to content</a>
    <div class="flex min-h-screen flex-col">
        @include('components.website.navbar', ['settings' => $settings])

        <main id="content" class="flex-1">
            {{ $slot }}
        </main>

        @include('components.website.footer', ['settings' => $settings])

        @include('components.website.fab-whatsapp', ['settings' => $settings])
        @include('components.website.sticky-cta', ['settings' => $settings])
    </div>

    @livewireScripts
</body>

</html>
