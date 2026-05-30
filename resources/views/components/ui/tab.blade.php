@props(['name', 'label'])

<li data-tab="{{ $name }}" class="relative" x-init="$nextTick(() => $root.__tabs?.updateIndicator?.())">
    <a href="javascript:" class="p-5 py-3 flex items-center relative transition-colors duration-200"
        :class="activeTab === '{{ $name }}' ? 'text-secondary' : ''" @click="setTab('{{ $name }}')">
        {{ $label }}
    </a>
</li>

<div x-show="activeTab === '{{ $name }}'" x-transition.opacity.duration.200ms class="mt-4">
    {{ $slot }}
</div>
