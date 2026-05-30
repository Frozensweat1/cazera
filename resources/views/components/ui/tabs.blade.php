@props([
    'default' => null,
    'name' => 'tabs', // used for localStorage key isolation
    'headers' => null,
])

<div x-data="tabs({
    defaultTab: '{{ $default }}',
    storageKey: '{{ $name }}'
})" x-init="init()" class="mb-5">
    <!-- TAB HEADER -->
    <div class="relative border-b border-white-light dark:border-[#191e3a]">

        <ul x-ref="tabList" class="flex flex-wrap relative">
            {{ $headers ?? '' }}
        </ul>

        <!-- Animated indicator -->
        <div class="absolute bottom-0 h-[2px] bg-secondary transition-all duration-300 ease-out" :style="indicatorStyle">
        </div>
    </div>

    <!-- CONTENT -->
    <div class="flex-1 text-sm mt-5">
        {{ $slot }}
    </div>
</div>
