@props(['label', 'value', 'hint' => null, 'icon' => 'book'])
<div {{ $attributes->merge(['class' => 'group relative overflow-hidden bg-[#FFFEFA] border border-line/80 rounded-xl p-5 flex items-start gap-4 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_14px_30px_-6px_rgba(10,25,47,0.08)] hover:border-gold/50']) }}>
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#FAF6ED] via-[#F4ECCF] to-[#EADAAA]/40 text-gold-dark border border-gold/30 transition-transform duration-300 group-hover:scale-105 shadow-xs">
        <x-icon :name="$icon" class="h-5 w-5" />
    </span>
    <div class="min-w-0">
        <p class="text-[11px] font-medium uppercase tracking-[0.14em] text-gray-500">{{ $label }}</p>
        <p class="mt-1 font-serif text-3xl font-semibold tracking-tight text-navy">{{ $value }}</p>
        @if ($hint)
            <p class="mt-0.5 text-xs text-gray-400">{{ $hint }}</p>
        @endif
    </div>
</div>
