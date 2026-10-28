@props(['tone' => 'success'])
@php
    $tones = [
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'danger'  => 'bg-rose-50 border-rose-200 text-rose-900',
        'info'    => 'bg-indigo-50 border-indigo-100 text-indigo-800',
    ];
@endphp
<div {{ $attributes->merge(['class' => 'rounded-lg border px-4 py-3 text-sm ' . $tones[$tone]]) }} role="alert">{{ $slot }}</div>
