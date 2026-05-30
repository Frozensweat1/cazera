@props(['name', 'maxWidth' => 'lg'])

@php
    $maxWidths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-xl',
        'xl' => 'max-w-3xl',
        '2xl' => 'max-w-5xl',
        '3xl' => 'max-w-6xl',
        '4xl' => 'max-w-7xl',
        '5xl' => 'max-w-[90rem]',
        'full' => 'max-w-full mx-4',
    ];

    $width = $maxWidths[$maxWidth] ?? $maxWidths['lg'];
@endphp

<div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail == '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail == '{{ $name }}') open = false">

    <!-- MODAL OVERLAY -->
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/60 z-[999] overflow-y-auto"
        style="display: none;">

        <!-- MODAL CONTAINER -->
        <div class="flex items-start justify-center min-h-screen px-4 py-8" @click.self="open = false">

            <!-- MODAL PANEL -->
            <div x-show="open" x-transition.duration.300ms
                class="panel border-0 p-0 rounded-lg overflow-hidden w-full {{ $width }}">

                <!-- HEADER -->
                <div class="flex items-center justify-between px-5 py-3 bg-[#fbfbfb] dark:bg-[#121c2c] border-b">

                    <h5 class="font-bold text-lg">
                        {{ $title ?? 'Modal Title' }}
                    </h5>

                    <button type="button" class="text-gray-500 hover:text-black dark:hover:text-white"
                        @click="open = false">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>

                </div>

                <!-- BODY -->
                <div class="p-5">

                    {{ $slot }}

                </div>

                <!-- FOOTER -->
                @isset($footer)
                    <div class="px-5 py-4 border-t bg-gray-50 dark:bg-[#121c2c]">

                        {{ $footer }}

                    </div>
                @endisset

            </div>

        </div>

    </div>

</div>
