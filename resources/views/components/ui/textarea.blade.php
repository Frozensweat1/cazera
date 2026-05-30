@props([
    'label' => null,
    'name',
])

<div>

    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block font-semibold">
            {{ $label }}
        </label>
    @endif

    <textarea id="{{ $name }}" name="{{ $name }}" rows="4"
        {{ $attributes->merge([
            'class' => 'form-textarea w-full',
        ]) }}></textarea>

    @error($name)
        <span class="text-danger text-sm mt-1 block">
            {{ $message }}
        </span>
    @enderror

</div>
