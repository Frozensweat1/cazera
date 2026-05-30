@props([
    'title' => null,
    'subtitle' => null,
])

@php
    $settings = \App\Support\WebsiteContent::settings();
    $brandName = $settings?->business_name ?: config('app.name', 'Cazera');
    $tagline = $settings?->tagline ?: 'Hospitality operations, refined.';
    $logo = $settings?->logo ? \App\Support\WebsiteContent::assetPath($settings->logo) : null;
    $favicon = $settings?->favicon ? \App\Support\WebsiteContent::assetPath($settings->favicon) : asset('favicon.ico');
    $pageTitle = trim(($title ? $title . ' - ' : '') . $brandName);

    $brandInitials = collect(explode(' ', $brandName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    $brandInitials = $brandInitials ?: mb_strtoupper(mb_substr($brandName, 0, 2));

    $user = auth()->user();
    $userName = $user?->name ?: $user?->email;
    $userInitials = $userName
        ? collect(explode(' ', $userName))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('')
        : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('assets/css/perfect-scrollbar.min.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('assets/css/style.css') }}">
    <link defer rel="stylesheet" type="text/css" media="screen" href="{{ asset('assets/css/animate.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-[#080706] font-sans text-slate-900 antialiased">
    <main class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(218,165,32,0.2),transparent_34%),linear-gradient(135deg,#080706_0%,#16110d_45%,#2a2119_100%)]"></div>
        <div class="absolute inset-0 opacity-[0.16] [background-image:linear-gradient(rgba(255,255,255,0.12)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.12)_1px,transparent_1px)] [background-size:48px_48px]"></div>

        <section class="relative z-10 grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(420px,540px)]">
            <aside class="hidden min-h-screen flex-col justify-between px-12 py-10 text-white lg:flex xl:px-16">
                <a href="{{ route('website.home') }}" class="inline-flex items-center gap-3 focus:outline-none focus:ring-2 focus:ring-[#d7b56d] focus:ring-offset-2 focus:ring-offset-[#080706]">
                    <span class="inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-white/15 bg-white/10 text-sm font-bold text-[#f5dfaa] shadow-2xl shadow-black/20 backdrop-blur">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="{{ $brandName }} logo" class="h-full w-full object-contain p-2">
                        @else
                            {{ $brandInitials }}
                        @endif
                    </span>
                    <span>
                        <span class="block text-lg font-semibold tracking-wide">{{ $brandName }}</span>
                        <span class="block text-xs uppercase tracking-[0.28em] text-[#d7b56d]">Hospitality ERP</span>
                    </span>
                </a>

                <div class="max-w-xl">
                    <p class="mb-5 text-sm uppercase tracking-[0.38em] text-[#d7b56d]">Backoffice Access</p>
                    <h1 class="font-serif text-5xl font-bold leading-tight text-white xl:text-6xl">
                        Run every branch with clarity and control.
                    </h1>
                    <p class="mt-6 max-w-lg text-base leading-8 text-white/68">
                        {{ $tagline }}
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-4 text-sm text-white/70">
                    <div class="border-t border-white/15 pt-4">
                        <span class="block text-lg font-semibold text-white">POS</span>
                        <span>Sales and registers</span>
                    </div>
                    <div class="border-t border-white/15 pt-4">
                        <span class="block text-lg font-semibold text-white">Stock</span>
                        <span>Inventory control</span>
                    </div>
                    <div class="border-t border-white/15 pt-4">
                        <span class="block text-lg font-semibold text-white">Teams</span>
                        <span>Branch access</span>
                    </div>
                </div>
            </aside>

            <div class="flex min-h-screen items-center justify-center px-5 py-8 sm:px-8 lg:bg-white/[0.03] lg:backdrop-blur-xl">
                <div class="w-full max-w-[460px]">
                    <div class="mb-8 flex items-center justify-between lg:hidden">
                        <a href="{{ route('website.home') }}" class="inline-flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl border border-white/15 bg-white/10 text-sm font-bold text-[#f5dfaa]">
                                @if ($logo)
                                    <img src="{{ $logo }}" alt="{{ $brandName }} logo" class="h-full w-full object-contain p-2">
                                @else
                                    {{ $brandInitials }}
                                @endif
                            </span>
                            <span class="text-base font-semibold text-white">{{ $brandName }}</span>
                        </a>

                        @if ($userInitials)
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/10 text-sm font-bold text-white">
                                {{ $userInitials }}
                            </span>
                        @endif
                    </div>

                    <div class="rounded-[2rem] border border-white/70 bg-white/95 p-6 shadow-2xl shadow-black/25 backdrop-blur sm:p-8">
                        <div class="mb-8">
                            <div class="mb-5 hidden items-center justify-between lg:flex">
                                <div class="inline-flex items-center gap-3">
                                    <span class="inline-flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-[#17110c] text-sm font-bold text-[#f5dfaa]">
                                        @if ($logo)
                                            <img src="{{ $logo }}" alt="{{ $brandName }} logo" class="h-full w-full object-contain p-2">
                                        @else
                                            {{ $brandInitials }}
                                        @endif
                                    </span>
                                    <span class="font-semibold text-slate-950">{{ $brandName }}</span>
                                </div>

                                @if ($userInitials)
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-700">
                                        {{ $userInitials }}
                                    </span>
                                @endif
                            </div>

                            @if ($title)
                                <h2 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $title }}</h2>
                            @endif

                            @if ($subtitle)
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $subtitle }}</p>
                            @endif
                        </div>

                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs text-white/55">
                        &copy; {{ now()->year }} {{ $brandName }}. Secure staff access.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('assets/js/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/tippy-bundle.umd.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    @livewireScripts
</body>

</html>
