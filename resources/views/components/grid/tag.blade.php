@props(['value'])

@php
    $colors = [
        'EXCLUSIVO'   => 'bg-red-100 text-red-800',
        'NOVO'        => 'bg-green-100 text-green-800',
        'IMPERDÍVEL'  => 'bg-orange-100 text-orange-800',
    ];

    // Se não encontrar a tag, usa cinza
    $classes = $colors[$value] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $classes }}">
    {{ $value }}
</span>
