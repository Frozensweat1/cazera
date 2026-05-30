@props(['status'])

@php
    $classes = match ($status) {
        'Complete' => 'bg-success',
        'Pending' => 'bg-secondary',
        'In Progress' => 'bg-primary',
        'Canceled' => 'bg-danger',
        default => 'bg-dark',
    };
@endphp

<span class="badge whitespace-nowrap {{ $classes }}">
    {{ $status }}
</span>
