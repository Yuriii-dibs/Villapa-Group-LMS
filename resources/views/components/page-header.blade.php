@props(['title', 'subtitle' => null, 'badge' => null])
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between border-b border-line/60 pb-6">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="font-serif text-2xl sm:text-3xl font-semibold tracking-tight text-navy">{!! $title !!}</h1>
            @if ($badge)
                <span class="inline-flex items-center rounded-full border border-gold/50 bg-gradient-to-r from-[#FAF6ED] to-[#F4ECCF] px-2.5 py-0.5 text-xs font-serif font-medium text-gold-dark shadow-2xs">
                    {{ $badge }}
                </span>
            @endif
        </div>
        <div class="mt-2.5 flex items-center gap-2">
            <span class="block h-0.5 w-12 bg-gradient-to-r from-gold via-gold-light to-transparent rounded-full"></span>
            <span class="h-1 w-1 rounded-full bg-gold/60"></span>
        </div>
        @if ($subtitle)
            <p class="mt-2.5 text-sm text-gray-500 max-w-2xl leading-relaxed">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions) && $actions->isNotEmpty())
        <div class="flex flex-wrap gap-2 shrink-0">{{ $actions }}</div>
    @endif
</div>
