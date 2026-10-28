@props(['padding' => 'p-5 sm:p-6'])
<div {{ $attributes->merge(['class' => "relative bg-[#FFFEFA]/95 backdrop-blur-sm border border-line/85 rounded-xl shadow-[0_2px_14px_rgba(10,25,47,0.03)] transition-all duration-300 hover:shadow-[0_16px_36px_-8px_rgba(10,25,47,0.08)] hover:border-gold/50 $padding"]) }}>
    {{ $slot }}
</div>
