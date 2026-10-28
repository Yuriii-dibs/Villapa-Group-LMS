@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'size' => 'md'])
@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium whitespace-nowrap transition-all duration-200 ease-out hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] shadow-sm hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-600 disabled:opacity-50';
    $sizes = ['sm' => 'px-3 py-1.5 text-xs', 'md' => 'px-4 py-2.5 text-sm', 'lg' => 'px-6 py-3 text-sm'];
    $variants = [
        'primary'   => 'bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white border border-gold/35 hover:border-gold/80 hover:shadow-[0_6px_20px_rgba(10,25,47,0.25)]',
        'secondary' => 'bg-[#FFFEFA] text-navy border border-line/90 hover:border-gold/60 hover:bg-[#FAF8F2] shadow-xs',
        'ghost'     => 'text-indigo-600 hover:bg-gold/10 hover:text-navy',
        'danger'    => 'bg-gradient-to-r from-[#92281D] to-[#A83226] text-white border border-rose-300/30 hover:brightness-110',
        'light'     => 'bg-[#FFFEFA] text-navy border border-gold/40 hover:bg-[#FAF8F2] shadow-xs',
    ];
    $classes = "$base {$sizes[$size]} {$variants[$variant]}";
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
