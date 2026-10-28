<x-layouts.guest>
    <x-slot:title>UEP - University of Eastern Pangasinan</x-slot:title>

    <x-public-header />

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-[#071321] via-[#0A192F] to-[#14365D] text-paper">
        <div class="relative z-10 mx-auto max-w-6xl px-4 pb-36 pt-16 sm:px-6 sm:pb-44 sm:pt-24 lg:px-8">
            <div class="inline-flex items-center gap-2.5 rounded-full border border-gold/40 bg-gold/10 px-4 py-1.5 text-xs font-serif tracking-widest text-gold uppercase backdrop-blur-sm shadow-xs">
                <span>UEP</span>
                <span class="opacity-40">&bull;</span>
                <span class="tracking-wider">University of Eastern Pangasinan</span>
            </div>
            <h1 class="mt-6 max-w-2xl font-serif text-4xl font-semibold leading-tight text-paper sm:text-5xl">
                Learn with purpose.<br><span class="text-indigo-200">Grow with confidence.</span>
            </h1>
            <p class="mt-5 max-w-xl text-base leading-relaxed text-indigo-100/90 font-light">
                Structured programs, live online classes, and direct conversations with your instructors.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button variant="light" size="lg" :href="route('programs.index')" class="shadow-[0_4px_24px_rgba(197,160,89,0.3)] border border-gold/50 font-medium">Explore Programs</x-button>
                @guest
                    <x-button size="lg" :href="route('login')" class="border border-paper/30 bg-transparent hover:bg-paper/10 backdrop-blur-sm">Sign In</x-button>
                @endguest
            </div>
        </div>
        <img src="{{ asset('images/waves/wave-hero.svg') }}" alt="" class="pointer-events-none absolute inset-x-0 bottom-0 h-28 w-full sm:h-44 object-cover object-bottom">
    </section>

    {{-- Academic Programs --}}
    <section class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <span class="block h-0.5 w-10 bg-gold"></span>
                <h2 class="mt-4 font-serif text-3xl font-semibold text-navy">Academic Programs</h2>
                <p class="mt-2 text-sm text-gray-500">Explore our undergraduate degree programs and specialized academic disciplines.</p>
            </div>
            <x-button variant="secondary" :href="route('programs.index')">Explore All Programs &rarr;</x-button>
        </div>

        @if ($programs->isEmpty())
            <x-empty-state title="No programs published yet" text="Check back soon for new learning opportunities." />
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($programs as $program)
                    @php
                        $code = match($program->slug) {
                            'bs-information-technology' => 'BSIT',
                            'bs-business-administration' => 'BSBA',
                            'bs-hospitality-management' => 'BSHM',
                            'bachelor-of-secondary-education' => 'BSEd',
                            'bs-criminology' => 'BSCrim',
                            'bs-midwifery' => 'BSM',
                            default => 'PROGRAM',
                        };
                    @endphp
                    <a href="{{ route('programs.show', $program->slug) }}"
                       class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-line/80 bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] transition-all duration-300 hover:-translate-y-1.5 hover:border-gold/50 hover:shadow-[0_16px_36px_-12px_rgba(10,25,47,0.12)]">
                        <div>
                            {{-- Card Hero Header --}}
                            <div class="relative h-36 w-full overflow-hidden bg-gradient-to-br from-[#071321] via-[#0A192F] to-[#14365D] p-5 flex flex-col justify-between">
                                <img src="{{ asset('images/waves/wave-panel.svg') }}" alt=""
                                     class="absolute inset-x-0 bottom-0 h-full w-full object-cover opacity-85 transition-transform duration-500 ease-out group-hover:scale-105 pointer-events-none">
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="inline-flex items-center rounded-lg bg-gold/20 px-2.5 py-1 text-xs font-mono font-bold text-gold border border-gold/40 backdrop-blur-sm">
                                        {{ $code }}
                                    </span>
                                    <span class="text-[11px] font-sans text-paper/70 font-light">4-Year Degree</span>
                                </div>
                                <span class="relative z-10 font-serif text-lg font-semibold leading-snug text-paper transition-transform duration-300 group-hover:translate-x-1">
                                    {{ $program->title }}
                                </span>
                            </div>

                            {{-- Card Body --}}
                            <div class="p-5 space-y-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-800 border border-indigo-100">
                                        {{ $program->category?->name ?? 'Academic Department' }}
                                    </span>
                                </div>
                                <p class="line-clamp-3 text-xs leading-relaxed text-gray-600">
                                    {{ $program->description }}
                                </p>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="flex items-center justify-between border-t border-line bg-ivory px-5 py-3 text-xs text-gray-500">
                            <span class="text-[11px] font-medium text-gray-500">Curriculum &amp; Overview</span>
                            <span class="font-semibold text-indigo-600 transition-colors duration-150 group-hover:text-navy">
                                View Program &rarr;
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Mission, Vision & Core Values Section --}}
    <section class="mx-auto w-full max-w-6xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="mb-10">
            <span class="block h-0.5 w-10 bg-gold"></span>
            <h2 class="mt-4 font-serif text-3xl font-semibold text-navy">Mission, Vision & Core Values</h2>
            <p class="mt-2 text-sm text-gray-500">The foundational pillars guiding the University of Eastern Pangasinan.</p>
        </div>

        {{-- Vision & Mission 2-Column Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Vision Card --}}
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-line/80 bg-[#FFFEFA] p-8 shadow-[0_2px_14px_rgba(10,25,47,0.03)] transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:shadow-[0_16px_36px_-12px_rgba(10,25,47,0.1)]">
                <div>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-gold/40 bg-gold/10 px-3 py-1 text-xs font-serif font-medium uppercase tracking-widest text-gold-dark">
                        Vision
                    </div>
                    <h3 class="font-serif text-2xl font-semibold text-navy">Our Vision</h3>
                    <p class="mt-4 text-sm sm:text-base leading-relaxed text-gray-600">
                        A leading local university known to develop excellent and virtuous professionals who are catalysts of progress for both the local and global communities.
                    </p>
                </div>
                <div class="mt-8 flex items-center gap-2 border-t border-line/60 pt-4 text-xs font-serif text-gold-dark">
                    <span>Catalysts of Global & Local Progress</span>
                </div>
            </div>

            {{-- Mission Card --}}
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-line/80 bg-[#FFFEFA] p-8 shadow-[0_2px_14px_rgba(10,25,47,0.03)] transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:shadow-[0_16px_36px_-12px_rgba(10,25,47,0.1)]">
                <div>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-gold/40 bg-gold/10 px-3 py-1 text-xs font-serif font-medium uppercase tracking-widest text-gold-dark">
                        Mission
                    </div>
                    <h3 class="font-serif text-2xl font-semibold text-navy">Our Mission</h3>
                    <p class="mt-4 text-sm sm:text-base leading-relaxed text-gray-600">
                        University of Eastern Pangasinan is dedicated to producing highly competent and morally responsible UEPIans through innovation, industry-focused education, sustainable research, and values-based curriculum. We commit to shaping ethical trailblazers bringing our principle-centered culture defining our values of <strong class="text-navy font-semibold">Maka-Diyos, Marunong and Malinis</strong>.
                    </p>
                </div>
                <div class="mt-8 flex items-center gap-2 border-t border-line/60 pt-4 text-xs font-serif text-gold-dark">
                    <span>Maka-Diyos &bull; Marunong &bull; Malinis</span>
                </div>
            </div>
        </div>

        {{-- Core Values Banner --}}
        <div class="relative mt-6 overflow-hidden rounded-2xl bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] border border-gold/40 p-8 sm:p-10 text-paper shadow-[0_12px_32px_-8px_rgba(10,25,47,0.25)]">
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 mb-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-gold animate-ping"></span>
                        <p class="text-xs font-serif uppercase tracking-[0.16em] text-gold">Institutional Creed</p>
                    </div>
                    <h3 class="mt-1 font-serif text-2xl sm:text-3xl font-semibold text-paper">Core Values</h3>
                    <p class="mt-1 text-xs text-indigo-200">The guiding principles ingrained in every UEPIan.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-10 border-t border-gold/20 pt-6 lg:border-t-0 lg:pt-0">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold/15 border border-gold/40 text-gold font-serif font-bold text-sm">
                            U
                        </span>
                        <div>
                            <span class="block font-serif text-lg font-semibold text-paper">Uprightness</span>
                            <span class="block text-xs text-indigo-200/80">Integrity & moral strength</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold/15 border border-gold/40 text-gold font-serif font-bold text-sm">
                            E
                        </span>
                        <div>
                            <span class="block font-serif text-lg font-semibold text-paper">Excellence</span>
                            <span class="block text-xs text-indigo-200/80">Quality & continuous growth</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold/15 border border-gold/40 text-gold font-serif font-bold text-sm">
                            P
                        </span>
                        <div>
                            <span class="block font-serif text-lg font-semibold text-paper">Passion</span>
                            <span class="block text-xs text-indigo-200/80">Dedication to serve</span>
                        </div>
                    </div>
                </div>
            </div>
            <img src="{{ asset('images/waves/wave-panel.svg') }}" alt="" class="pointer-events-none absolute inset-x-0 bottom-0 h-16 w-full opacity-35 object-cover object-bottom">
        </div>
    </section>

    <x-public-footer />
</x-layouts.guest>
