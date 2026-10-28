@props(['tone' => 'neutral'])
@php
    $tones = [
        'neutral' => 'bg-ivory text-gray-600 border-line',
        'brand'   => 'bg-gradient-to-r from-indigo-50 to-indigo-100/70 text-indigo-800 border-indigo-200/70',
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'danger'  => 'bg-rose-50 text-rose-700 border-rose-200',
        'gold'    => 'bg-gradient-to-r from-[#FAF6ED] to-[#F4ECCF] text-[#6A4F23] border-gold/45 shadow-2xs',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-medium tracking-wide ' . ($tones[$tone] ?? $tones['neutral'])]) }}>{{ $slot }}</span>
