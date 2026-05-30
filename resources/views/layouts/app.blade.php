<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    @include('partials.head')

    @livewireStyles
</head>

<body x-data="main" class="relative overflow-x-hidden font-nunito text-sm font-normal antialiased"
    :class="[$store.app.sidebar ? 'toggle-sidebar' : '', $store.app.theme === 'dark' || $store.app.isDarkMode ? 'dark' : '',
        $store.app.menu, $store.app.layout, $store.app.rtlClass
    ]">
    <!-- sidebar menu overlay -->
    <div x-cloak="" class="fixed inset-0 z-50 bg-[black]/60 lg:hidden" :class="{ 'hidden': !$store.app.sidebar }"
        @click="$store.app.toggleSidebar()">
    </div>

    <!-- screen loader -->
    @include('partials.preloader')

    <!-- scroll to top button -->
    @include('partials.scroll-to-top')

    <!-- start theme customizer section -->
    @include('partials.theme-customizer')
    <!-- end theme customizer section -->

    <div class="main-container min-h-screen text-black dark:text-white-dark" :class="[$store.app.navbar]">
        <!-- start sidebar section -->
        <x-layout.sidebar />
        <!-- end sidebar section -->

        <div class="main-content flex min-h-screen flex-col">
            <!-- start header section -->
            <x-layout.header />
            <!-- end header section -->

            <div class="animate__animated p-6" :class="[$store.app.animation]">
                <!-- start main content section -->
                {{ $slot }}
                <!-- end main content section -->
            </div>

            <!-- start footer section -->
            <x-layout.footer />
            <!-- end footer section -->
        </div>
    </div>

    @include('partials.scripts')

    @livewireScripts

</body>

</html>
