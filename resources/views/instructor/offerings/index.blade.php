<x-layouts.app>
    <x-slot:title>My Classes – Faculty Portal</x-slot:title>

    <x-page-header
        title="My Classes"
        subtitle="Manage your teaching offerings, student rosters, and online Google Meet links."
    >
        <x-slot:actions>
            <a href="{{ route('schedule.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-navy hover:border-gold/50 transition shadow-2xs">
                <x-icon name="calendar" class="h-4 w-4 text-gold" />
                <span>Weekly Schedule</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    @if ($offerings->isEmpty())
        <x-card>
            <x-empty-state
                title="No class offerings assigned"
                text="You are not currently assigned as an instructor to any active subject offerings."
            />
        </x-card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($offerings as $offering)
                @php
                    $subj = $offering->subject;
                    $schedules = $offering->schedules ?: ($subj?->schedules ?: []);
                @endphp
                <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-line/90 bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] hover:border-gold/60 transition duration-200">
                    {{-- Header Banner --}}
                    <div class="bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] p-5 text-paper">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="inline-flex items-center rounded-lg bg-gold/15 px-2.5 py-1 text-xs font-mono font-bold text-gold border border-gold/30">
                                {{ $subj?->subject_code ?? 'CODE' }}
                            </span>
                            <span class="inline-flex items-center rounded-md bg-white/10 px-2.5 py-0.5 text-xs font-semibold text-indigo-100">
                                {{ $offering->section?->name ?? 'Section' }}
                            </span>
                        </div>
                        <h3 class="font-serif text-base font-bold text-paper line-clamp-2">
                            {{ $subj?->title ?? 'Subject Title' }}
                        </h3>
                        <p class="text-xs text-indigo-200/80 mt-1">
                            {{ $offering->academicTerm?->name ?? 'Current Term' }}
                        </p>
                    </div>

                    {{-- Body Details --}}
                    <div class="flex-1 p-5 space-y-4">
                        {{-- Schedules --}}
                        <div>
                            <span class="block text-[10px] uppercase font-bold tracking-wider text-gray-400 mb-1">Weekly Slots</span>
                            <div class="space-y-1">
                                @forelse ($schedules as $slot)
                                    <div class="flex items-center justify-between text-xs rounded-lg bg-gray-50 px-2.5 py-1.5 border border-line/60">
                                        <span class="font-bold text-navy">{{ $slot['day'] ?? '' }} ({{ $slot['time_start'] ?? '' }} - {{ $slot['time_end'] ?? '' }})</span>
                                        <span class="text-gray-500 font-mono">{{ $slot['room'] ?? $offering->room ?? 'Campus' }}</span>
                                    </div>
                                @empty
                                    <span class="text-xs text-gray-400 italic">No slots scheduled yet</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Meet Link Status --}}
                        <div class="pt-2 border-t border-line/60 flex items-center justify-between text-xs">
                            <span class="text-gray-500 font-medium">Google Meet:</span>
                            @if ($offering->meet_link)
                                <a href="{{ $offering->meet_link }}" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-600 hover:underline truncate max-w-[150px]">
                                    Active Link &rarr;
                                </a>
                            @else
                                <span class="text-gray-400 italic">Not set</span>
                            @endif
                        </div>

                        {{-- Enrolled Students Count --}}
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 font-medium">Enrolled Students:</span>
                            <span class="inline-flex items-center rounded-full bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 font-bold text-indigo-700">
                                {{ $offering->students_count }} learners
                            </span>
                        </div>
                    </div>

                    {{-- Footer Action --}}
                    <div class="p-4 bg-gray-50/80 border-t border-line/70 flex items-center justify-between">
                        <a href="{{ route('instructor.offerings.show', $offering) }}"
                           class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#0A192F] hover:bg-[#14365D] text-gold text-xs font-bold transition shadow-2xs">
                            <x-icon name="users" class="h-3.5 w-3.5" />
                            <span>View Class & Roster</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
