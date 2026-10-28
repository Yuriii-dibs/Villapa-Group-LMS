@props(['dark' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 group']) }}>
    <img src="{{ asset('images/logo.png') }}" alt="University of Eastern Pangasinan" class="h-9 w-9 shrink-0 object-contain rounded-full shadow-xs transition-transform duration-300 group-hover:scale-105">
    <span class="flex flex-col">
        <span class="font-serif text-base font-semibold leading-tight tracking-wide {{ $dark ? 'text-paper' : 'text-navy' }}">
            UEP
        </span>
        <span class="text-[10px] font-sans tracking-tight {{ $dark ? 'text-indigo-200/80' : 'text-gray-500' }}">University of Eastern Pangasinan</span>
    </span>
</span>
