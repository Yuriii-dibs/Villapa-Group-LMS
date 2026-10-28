<x-layouts.app>
    <x-slot:title>My Subjects – UEP LMS</x-slot:title>

    <x-page-header
        title="My Subjects"
        subtitle="Attend your online classes, access review files, and submit assignments."
    >
        <x-slot:actions>
            @if ($programCode)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-gold/40 bg-[#0A192F] px-4 py-1.5 text-xs font-bold text-gold shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-gold"></span>
                    Enrolled Program: {{ $programCode }}
                </span>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-10">

        {{-- ── 1. ENROLLED ACADEMIC SUBJECTS ────────────────────────────────── --}}
        <section>
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#0A192F] px-3.5 py-1 text-xs font-bold text-gold border border-gold/30 shadow-xs">
                        <x-icon name="book" class="h-3.5 w-3.5 text-gold" /> Enrolled Subjects
                    </span>
                    <span class="text-xs text-gray-500 font-medium">Academic Year 2026–2027</span>
                </div>
                @if ($subjects->isNotEmpty())
                    <span class="text-xs text-gray-400 font-medium">{{ $subjects->count() }} Subjects</span>
                @endif
            </div>

            @if ($subjects->isEmpty())
                <x-empty-state
                    title="No subjects enrolled"
                    text="You are not currently enrolled in any subjects. A School Administrator must enroll you into your section or subject offerings before they appear here."
                />
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($subjects as $subject)
                        <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-line/90 bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] transition-all duration-300 hover:-translate-y-1 hover:border-gold/60 hover:shadow-[0_16px_36px_-12px_rgba(10,25,47,0.12)]">
                            
                            {{-- Top Header Stripe --}}
                            <div class="relative bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] p-5 text-paper">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <span class="inline-flex items-center rounded-lg bg-gold/15 px-2.5 py-1 text-xs font-mono font-bold text-gold border border-gold/30">
                                        {{ $subject->subject_code }}
                                    </span>
                                    <span class="text-[11px] font-sans font-medium text-indigo-200">
                                        Block {{ $subject->block ?? '1' }}
                                    </span>
                                </div>
                                <h3 class="font-serif text-lg font-semibold text-paper leading-snug line-clamp-2">
                                    {{ $subject->title }}
                                </h3>
                                <p class="mt-1 text-xs text-indigo-200/90 truncate">
                                    Instructor: {{ $subject->instructor ?? 'Faculty' }}
                                </p>
                            </div>

                            {{-- Subject Details / Schedules --}}
                            <div class="flex-1 p-5 space-y-4">
                                {{-- Schedule Badges --}}
                                <div>
                                    <span class="block text-[10px] uppercase font-bold tracking-wider text-gray-400 mb-1.5">Schedule & Room</span>
                                    <div class="space-y-1">
                                        @foreach ($subject->schedules as $slot)
                                            <div class="flex items-center justify-between text-xs rounded-lg bg-gray-50 px-2.5 py-1.5 border border-line/60">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-navy">{{ $slot['day'] }}</span>
                                                    <span class="text-gray-500">({{ $slot['time_start'] }} - {{ $slot['time_end'] }})</span>
                                                </div>
                                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold {{ str_contains($slot['room'] ?? '', 'ONLINE') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700' }}">
                                                    {{ str_contains($slot['room'] ?? '', 'ONLINE') ? 'Google Meet' : ($slot['room'] ?? 'Campus') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Counts: Files & Assignments --}}
                                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-line/60 text-xs text-gray-600">
                                    <div class="flex items-center gap-1.5 rounded-lg bg-[#FAF8F5] p-2 border border-line/60">
                                        <x-icon name="book" class="h-4 w-4 text-gold shrink-0" />
                                        <div>
                                            <span class="font-bold text-navy block leading-tight">{{ $subject->resources->count() }}</span>
                                            <span class="text-[10px] text-gray-400">Review Files</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 rounded-lg bg-[#FAF8F5] p-2 border border-line/60">
                                        <x-icon name="inbox" class="h-4 w-4 text-gold shrink-0" />
                                        <div>
                                            <span class="font-bold text-navy block leading-tight">{{ $subject->assignments->count() }}</span>
                                            <span class="text-[10px] text-gray-400">Activities</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Quick Actions --}}
                            <div class="border-t border-line/80 bg-ivory px-5 py-3.5 flex items-center justify-between gap-3">
                                <a href="{{ $subject->google_meet_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-900 transition"
                                   title="Join Google Meet Live Classroom">
                                    <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
                                    </svg>
                                    Google Meet
                                </a>

                                <a href="{{ route('student.classes.show', $subject) }}"
                                   class="inline-flex items-center gap-1 rounded-xl bg-gradient-to-r from-[#0B213D] to-[#1B4965] px-3.5 py-1.5 text-xs font-bold text-gold border border-gold/30 hover:border-gold/70 shadow-2xs transition">
                                    Classroom &rarr;
                                </a>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ── 2. UPCOMING / LIVE SESSIONS ───────────────────────────────────── --}}
        <section class="pt-6 border-t border-line/70">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#0A192F] px-3.5 py-1 text-xs font-bold text-gold border border-gold/30 shadow-xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Scheduled Online Sessions
                    </span>
                    <span class="text-xs text-gray-500 font-medium hidden sm:inline">
                        Live conferences, webinars & virtual lectures
                    </span>
                </div>
                @if ($upcoming->isNotEmpty())
                    <span class="text-xs text-gray-500 font-medium bg-[#FAF8F5] px-3 py-1 rounded-full border border-line/70 self-start sm:self-auto">
                        {{ $upcoming->count() }} {{ Str::plural('session', $upcoming->count()) }}
                    </span>
                @endif
            </div>

            @if ($upcoming->isEmpty())
                <div class="rounded-2xl border border-dashed border-line/90 bg-[#FFFEFA] p-8 sm:p-10 text-center shadow-2xs">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-navy/5 text-navy border border-line/60 mb-3">
                        <x-icon name="video" class="h-6 w-6 text-gold" />
                    </div>
                    <h4 class="font-serif text-base sm:text-lg font-semibold text-navy">No scheduled live sessions right now</h4>
                    <p class="mt-1.5 max-w-md mx-auto text-xs sm:text-sm text-gray-500 leading-relaxed">
                        You can join your regular class sessions using the direct Google Meet links provided on your subject cards above.
                    </p>
                </div>
            @else
                <div class="space-y-4 sm:space-y-5">
                    @foreach ($upcoming as $entry)
                        @php
                            $session = $entry['session'];
                            $course  = $entry['course'];
                            $isLive  = $session->isLive();
                            $canJoin = $isLive || $session->canStudentJoin();
                        @endphp
                        <div class="group relative flex flex-col sm:flex-row items-stretch rounded-2xl border border-line/90 bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] hover:border-gold/60 hover:shadow-[0_12px_30px_-8px_rgba(10,25,47,0.10)] transition-all duration-300 overflow-hidden">
                            
                            {{-- Date / Live Indicator Column --}}
                            <div class="flex flex-row sm:flex-col items-center justify-between sm:justify-center px-5 py-3.5 sm:py-5 sm:w-28 sm:min-w-[112px] shrink-0 {{ $isLive ? 'bg-gradient-to-br from-red-600 to-red-700 text-white' : 'bg-gradient-to-br from-[#071321] via-[#0A192F] to-[#14365D] text-white border-b sm:border-b-0 sm:border-r border-line/40' }}">
                                @if ($isLive)
                                    <div class="flex items-center sm:flex-col gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">
                                            <span class="h-1.5 w-1.5 rounded-full bg-white animate-ping"></span> Live Now
                                        </span>
                                    </div>
                                    <span class="text-xs sm:text-[11px] text-red-100 font-medium">In Progress</span>
                                @else
                                    <div class="flex items-center sm:flex-col gap-1 sm:gap-0 text-center">
                                        <span class="text-[10px] sm:text-[11px] font-bold text-gold uppercase tracking-widest">
                                            {{ $session->starts_at->format('M') }}
                                        </span>
                                        <span class="text-xl sm:text-2xl font-black text-white font-serif leading-none my-0 sm:my-0.5">
                                            {{ $session->starts_at->format('d') }}
                                        </span>
                                    </div>
                                    <span class="text-xs sm:text-[11px] text-indigo-200/90 font-medium">
                                        {{ $session->starts_at->format('l') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Session Info Details Column --}}
                            <div class="flex-1 p-5 sm:p-6 flex flex-col justify-center gap-2 sm:gap-2.5">
                                <div class="flex flex-wrap items-center gap-2.5">
                                    @if ($isLive)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700 border border-red-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-600 animate-ping"></span> Live Session
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Scheduled
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                                        <x-icon name="clock" class="h-3.5 w-3.5 text-gray-400" />
                                        {{ $session->starts_at->format('g:i A') }}
                                        @if ($session->ends_at) &ndash; {{ $session->ends_at->format('g:i A') }} @endif
                                    </span>
                                </div>

                                <div>
                                    <h3 class="font-serif text-base sm:text-lg font-bold text-navy leading-snug group-hover:text-gold transition-colors">
                                        {{ $session->title }}
                                    </h3>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-600">
                                        <span class="font-semibold text-navy flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gold"></span>
                                            {{ $course->title }}
                                        </span>
                                        @if (!empty($course->instructor))
                                            <span class="text-gray-300">&bull;</span>
                                            <span class="text-gray-500">Faculty: {{ $course->instructor->name ?? 'Instructor' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Action / Join CTA Column --}}
                            <div class="flex items-center justify-center px-5 py-4 sm:px-6 sm:py-5 bg-[#FAF8F5]/80 border-t sm:border-t-0 sm:border-l border-line/70 shrink-0 sm:w-52">
                                @if ($canJoin)
                                    <a href="{{ route('classes.show', $session) }}"
                                       class="inline-flex items-center justify-center gap-2 rounded-xl {{ $isLive ? 'bg-red-600 hover:bg-red-700 text-white shadow-xs' : 'bg-gradient-to-r from-[#0B213D] to-[#1B4965] hover:from-[#071321] hover:to-[#14365D] text-gold border border-gold/40 hover:border-gold shadow-2xs' }} px-5 py-2.5 text-xs sm:text-sm font-bold transition w-full sm:w-auto">
                                        {{ $isLive ? 'Join Live Class' : 'Enter Class' }} &rarr;
                                    </a>
                                @else
                                    <div class="w-full sm:w-auto text-center px-3.5 py-2 rounded-xl bg-gray-100/90 border border-line/60">
                                        <span class="block text-xs font-semibold text-gray-600">Opens 15 min</span>
                                        <span class="block text-[11px] text-gray-400">before start time</span>
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</x-layouts.app>
