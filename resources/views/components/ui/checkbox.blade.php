@props(['label', 'name'])

<label class="flex items-center gap-2 cursor-pointer">

    <input type="checkbox" id="{{ $name }}" name="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'form-checkbox',
        ]) }}>

    <span class="text-white-dark">
        {{ $label }}
    </span>

</label>
