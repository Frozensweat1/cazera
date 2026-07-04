@props(['amount' => 0, 'decimals' => 2])

GHS {{ number_format((float) $amount, (int) $decimals) }}
