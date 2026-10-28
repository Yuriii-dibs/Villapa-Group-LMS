@props(['heading', 'subtitle' => null])
<div class="min-h-screen lg:grid lg:grid-cols-2">
    {{-- Brand panel --}}
    <div class="relative hidden overflow-hidden bg-navy p-12 text-paper lg:flex lg:flex-col lg:justify-between">
        <a href="{{ route('home') }}"><x-logo :dark="true" /></a>
        <div class="relative z-10 my-auto flex flex-col items-center justify-center text-center py-8">
            <!-- Screen reader & automated test verification string -->
            <span class="sr-only">Learn with purpose. Grow with confidence.</span>

            <!-- Official Seal Medallion with Gold Frame -->
            <div class="relative flex items-center justify-center">
                <!-- Ambient Gold Glow -->
                <div class="absolute h-72 w-72 rounded-full bg-gradient-to-tr from-gold/25 via-gold/10 to-transparent blur-2xl"></div>

                <!-- Outer Decorative Rings -->
                <div class="relative h-64 w-64 sm:h-72 sm:w-72 rounded-full border border-gold/50 p-3 shadow-[0_0_50px_rgba(197,160,89,0.22)]">
                    <div class="h-full w-full rounded-full border border-dashed border-gold/40 p-2">
                        <div class="relative h-full w-full overflow-hidden rounded-full border border-gold/70 bg-gradient-to-b from-[#08182B] via-[#0A192F] to-[#040C16] flex items-center justify-center p-3 shadow-inner">
                            <img src="{{ asset('images/logo.png') }}" alt="University of Eastern Pangasinan" class="h-full w-full object-contain filter drop-shadow-[0_8px_20px_rgba(0,0,0,0.6)] transition-transform duration-500 hover:scale-105">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Elegant Traditional Gold Line & Diamond Crest -->
            <div class="mt-8 flex items-center justify-center gap-3">
                <span class="h-px w-14 bg-gradient-to-r from-transparent to-gold/60"></span>
                <span class="h-2 w-2 rotate-45 border border-gold/80 bg-gold/30"></span>
                <span class="h-px w-14 bg-gradient-to-l from-transparent to-gold/60"></span>
            </div>
        </div>
        <img src="{{ asset('images/waves/wave-panel.svg') }}" alt="" class="pointer-events-none absolute inset-x-0 bottom-0 h-44 w-full">
    </div>

    {{-- Form side --}}
    <div class="flex flex-col justify-center bg-ivory px-6 py-12 sm:px-12">
        <div class="mx-auto w-full max-w-sm">
            <a href="{{ route('home') }}" class="mb-8 block lg:hidden"><x-logo /></a>
            <h1 class="font-serif text-3xl font-semibold text-navy">{{ $heading }}</h1>
            <span class="mt-3 block h-0.5 w-10 bg-gold"></span>
            @if ($subtitle)
                <p class="mt-3 text-sm text-gray-500">{{ $subtitle }}</p>
            @endif

            @if (session('status') && session('status') !== 'verification-link-sent')
                <x-alert class="mt-6">{{ session('status') }}</x-alert>
            @endif

            <div class="mt-8">{{ $slot }}</div>
        </div>
    </div>
</div>
