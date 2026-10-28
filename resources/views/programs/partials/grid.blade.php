{{-- Search Filter --}}
<form method="GET" action="{{ route('programs.index') }}"
      class="mb-8 flex flex-col gap-4 rounded-xl border border-line/80 bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] p-6 sm:flex-row sm:items-end">
    <div class="flex-1">
        <label for="search" class="block text-[11px] font-medium uppercase tracking-[0.14em] text-gray-500">Search</label>
        <input type="text" name="search" id="search" value="{{ $currentSearch }}"
               placeholder="e.g. BSIT, Criminology, Midwifery…"
               class="mt-1.5 block w-full rounded-lg border border-line/90 bg-[#FAF8F2] px-3.5 py-2.5 text-sm focus:border-gold/70 focus:bg-[#FFFEFA] focus:ring-1 focus:ring-gold/70 transition">
    </div>
    <div class="flex items-end gap-2">
        <x-button type="submit" class="flex-1 sm:flex-none">Search</x-button>
        @if ($currentSearch)
            <x-button :href="route('programs.index')" variant="secondary">Reset</x-button>
        @endif
    </div>
</form>

@if ($programs->isEmpty())
    <x-empty-state title="No programs found" text="Try a different keyword or check back later.">
        <x-button :href="route('programs.index')">View all programs</x-button>
    </x-empty-state>
@else
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($programs as $program)
            <a href="{{ route('programs.show', $program->slug) }}"
               class="group flex flex-col overflow-hidden rounded-2xl border border-line/80 bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] transition-all duration-300 hover:-translate-y-1.5 hover:border-gold/50 hover:shadow-[0_16px_36px_-12px_rgba(10,25,47,0.12)]">

                {{-- Card Hero --}}
                @if ($program->thumbnail_path)
                    <div class="overflow-hidden">
                        <img src="{{ asset('storage/' . $program->thumbnail_path) }}"
                             alt="{{ $program->title }}"
                             class="h-40 w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                    </div>
                @else
                    <div class="relative h-40 w-full overflow-hidden bg-gradient-to-br from-[#071321] via-[#0A192F] to-[#14365D]">
                        <img src="{{ asset('images/waves/wave-panel.svg') }}" alt=""
                             class="absolute inset-x-0 bottom-0 h-full w-full object-cover opacity-80 transition-transform duration-500 ease-out group-hover:scale-105">
                        <span class="absolute left-4 top-4 font-serif text-xl font-semibold leading-snug text-paper transition-transform duration-300 group-hover:translate-x-1">
                            {{ Str::limit($program->title, 40) }}
                        </span>
                    </div>
                @endif

                {{-- Card Body --}}
                <div class="flex flex-1 flex-col p-5">
                    <div class="mb-2 flex items-center gap-2">
                        <x-badge tone="brand">{{ $program->category->name }}</x-badge>
                    </div>
                    <h3 class="font-serif text-lg font-semibold leading-snug text-navy">
                        {{ $program->title }}
                    </h3>
                    @if ($program->description)
                        <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ Str::limit($program->description, 100) }}</p>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="flex items-center justify-end border-t border-line bg-ivory px-5 py-3 text-xs text-gray-500">
                    <span class="font-medium text-indigo-600 transition-colors duration-150 group-hover:text-navy">
                        View program &rarr;
                    </span>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-10">{{ $programs->links() }}</div>
@endif
