@props(['title', 'text' => null])
<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-line bg-paper px-6 py-10 text-center']) }}>
    <img src="{{ asset('images/waves/wave-empty.svg') }}" alt="" class="mx-auto mb-4 h-10 w-auto opacity-90">
    <p class="font-serif text-lg font-semibold text-navy">{{ $title }}</p>
    @if ($text)
        <p class="mt-1 text-sm text-gray-500">{{ $text }}</p>
    @endif
    @if (!empty(trim($slot)))
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
