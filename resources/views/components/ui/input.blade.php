@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
])

<div>

    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="form-label mb-2">
            {{ $label }}
        </label>
    @endif

    <input type="{{ $type }}"
        @if ($name) id="{{ $name }}"
            name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'form-input w-full',
        ]) }}>

    @if ($name)
        @error($name)
            <span class="text-danger text-sm">
                {{ $message }}
            </span>
        @enderror
    @endif

</div>
