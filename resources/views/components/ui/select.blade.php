@props([
    'label' => null,
    'name' => null,
])

<div>

    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="form-label mb-2">
            {{ $label }}
        </label>
    @endif

    <select @if ($name) id="{{ $name }}"
            name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'form-select w-full',
        ]) }}>

        {{ $slot }}

    </select>

    @if ($name)
        @error($name)
            <span class="text-danger text-sm">
                {{ $message }}
            </span>
        @enderror
    @endif

</div>
