@props([
    'width' => 'w-48',
])

<div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">

    <!-- TRIGGER -->
    <button type="button"
        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-transparent text-gray-500 transition hover:border-gray-200 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:text-gray-300 dark:hover:border-white/10 dark:hover:bg-[#1b2e4b] dark:hover:text-white"
        aria-haspopup="menu"
        :aria-expanded="open.toString()"
        @click="open = !open">
        <span class="sr-only">Open row actions</span>
        <x-heroicon-o-ellipsis-vertical class="h-5 w-5" />
    </button>

    <!-- DROPDOWN -->
    <div x-cloak x-show="open" x-transition
        class="absolute right-0 z-50 mt-2 {{ $width }} overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl shadow-slate-900/10 dark:border-[#253b5c] dark:bg-[#1b2e4b]"
        role="menu">

        {{ $slot }}

    </div>

</div>
