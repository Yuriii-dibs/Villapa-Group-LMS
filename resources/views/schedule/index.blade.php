<x-layouts.app>
    <x-slot:title>My Schedule – UEP LMS</x-slot:title>

    <x-page-header
        title="Class Schedule"
        subtitle="Organized day-by-day timetable for your enrolled academic subjects."
    >
        <x-slot:actions>
            <a href="{{ route('calendar.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-gold/50 bg-[#0A192F] px-4 py-2.5 text-xs sm:text-sm font-semibold text-gold hover:bg-[#14365D] hover:text-white transition shadow-sm">
                <x-icon name="calendar" class="h-4 w-4" /> View Academic Calendar
            </a>
        </x-slot:actions>
    </x-page-header>

    @if (!$programName && $subjects->isEmpty())
        {{-- No program assigned and no enrolled subjects --}}
        <x-card>
            <x-empty-state
                title="No enrolled subjects or program assigned"
                text="An administrator needs to enroll you into your subjects before your schedule can be shown."
            />
        </x-card>
    @else
        {{-- ── PROGRAM & COHORT SUMMARY BANNER ─────────────────────────────── --}}
        @php
            $firstSubject = $subjects->first();
            $blockNumber  = $firstSubject?->block ?? 'J';
            $yearLevel    = $firstSubject?->year_level ?? '3';
            $sectionCode  = $firstSubject?->section ?? 'IT3-J';
        @endphp

        <div class="mb-8 rounded-2xl bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] p-5 sm:p-6 text-paper shadow-md border border-gold/30">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center rounded-lg bg-gold/20 px-3 py-1 font-mono text-xs font-bold text-gold border border-gold/40">
                            {{ $programName }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-gray-200">
                            Year {{ $yearLevel }} &bull; Section {{ $sectionCode }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-gray-200">
                            Block {{ $blockNumber }}
                        </span>
                    </div>
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-white tracking-tight">
                        {{ $user->program?->name ?? 'Enrolled Degree Program' }}
                    </h2>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="rounded-xl bg-white/10 backdrop-blur-xs px-4 py-2 border border-white/15 text-center">
                        <span class="block font-mono text-lg font-bold text-gold">{{ $subjects->count() }}</span>
                        <span class="text-[10px] uppercase tracking-wider text-gray-300 font-medium">Subjects</span>
                    </div>
                    <div class="rounded-xl bg-white/10 backdrop-blur-xs px-4 py-2 border border-white/15 text-center">
                        <span class="block font-mono text-lg font-bold text-white">{{ $totalSessions }}</span>
                        <span class="text-[10px] uppercase tracking-wider text-gray-300 font-medium">Weekly Classes</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($subjects->isEmpty())
            <x-card>
                <x-empty-state
                    title="No enrolled subjects found"
                    text="You are not currently enrolled in any subject offerings. An administrator must enroll you into your section or subjects before your schedule appears."
                />
            </x-card>
        @else
            {{-- ── INTERACTIVE VIEW SWITCHER & DAY FILTER ─────────────────────── --}}
            <div x-data="{
                viewMode: 'day',
                activeDay: 'all',
                today: '{{ $todayName }}',
                filterDay(day) {
                    this.activeDay = day;
                }
            }" class="space-y-6">

                {{-- Control Bar: Tabs & View Switcher --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-line/70">
                    {{-- Day Filter Pills --}}
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <button type="button"
                                @click="filterDay('all')"
                                :class="activeDay === 'all'
                                    ? 'bg-[#0A192F] text-gold border-gold/50 shadow-sm'
                                    : 'bg-white text-gray-600 hover:text-navy hover:bg-gray-50 border-gray-200'"
                                class="inline-flex items-center gap-1.5 rounded-xl border px-3.5 py-1.5 text-xs font-bold transition">
                            <span>All Days</span>
                            <span :class="activeDay === 'all' ? 'bg-gold/25 text-gold' : 'bg-gray-100 text-gray-500'"
                                  class="rounded-full px-1.5 py-0.2 text-[10px] font-mono">
                                {{ $totalSessions }}
                            </span>
                        </button>

                        @foreach ($daysOfWeek as $day)
                            @php
                                $dayCount = count($scheduleByDay[$day] ?? []);
                                $isToday = ($day === $todayName);
                            @endphp
                            <button type="button"
                                    @click="filterDay('{{ $day }}')"
                                    :class="activeDay === '{{ $day }}'
                                        ? 'bg-[#0A192F] text-gold border-gold/50 shadow-sm'
                                        : 'bg-white text-gray-600 hover:text-navy hover:bg-gray-50 border-gray-200'"
                                    class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-bold transition relative">
                                <span>{{ substr($day, 0, 3) }}</span>
                                @if ($isToday)
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                @endif
                                <span :class="activeDay === '{{ $day }}' ? 'bg-gold/25 text-gold' : 'bg-gray-100 text-gray-500'"
                                      class="rounded-full px-1.5 py-0.2 text-[10px] font-mono">
                                    {{ $dayCount }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- View Switcher (Day Timetable vs Subject Catalog) --}}
                    <div class="flex items-center rounded-xl bg-gray-100 p-1 border border-gray-200 self-start sm:self-auto shrink-0">
                        <button type="button"
                                @click="viewMode = 'day'"
                                :class="viewMode === 'day' ? 'bg-white text-navy font-bold shadow-xs' : 'text-gray-500 hover:text-navy'"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs transition">
                            <x-icon name="calendar" class="h-3.5 w-3.5" />
                            <span>Day by Day</span>
                        </button>
                        <button type="button"
                                @click="viewMode = 'table'"
                                :class="viewMode === 'table' ? 'bg-white text-navy font-bold shadow-xs' : 'text-gray-500 hover:text-navy'"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs transition">
                            <x-icon name="book" class="h-3.5 w-3.5" />
                            <span>Subject List</span>
                        </button>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════════════════════════ --}}
                {{-- VIEW 1: ORGANIZED DAY-BY-DAY TIMETABLE (DEFAULT)                 --}}
                {{-- ════════════════════════════════════════════════════════════════ --}}
                <div x-show="viewMode === 'day'" class="space-y-6">
                    @foreach ($daysOfWeek as $day)
                        @php
                            $classes = $scheduleByDay[$day] ?? [];
                            $isToday = ($day === $todayName);
                        @endphp

                        <div x-show="activeDay === 'all' || activeDay === '{{ $day }}'"
                             class="rounded-2xl border border-line/80 bg-white p-5 sm:p-6 shadow-[0_2px_14px_rgba(10,25,47,0.03)] transition">

                            {{-- Day Section Header --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-5 border-b border-line/60">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $isToday ? 'bg-gold/20 text-[#0A192F] border border-gold/40' : 'bg-gray-100 text-gray-700' }} font-bold text-xs uppercase font-mono">
                                        {{ substr($day, 0, 3) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-serif text-lg sm:text-xl font-bold text-navy">
                                                {{ $day }}
                                            </h3>
                                            @if ($isToday)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    Today
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ count($classes) }} {{ count($classes) === 1 ? 'class session' : 'class sessions' }} scheduled
                                        </p>
                                    </div>
                                </div>

                                <span class="text-xs font-semibold text-gray-400 self-start sm:self-auto">
                                    {{ count($classes) > 0 ? 'Sorted chronologically' : 'Free day' }}
                                </span>
                            </div>

                            {{-- Classes for this day --}}
                            @if (empty($classes))
                                <div class="rounded-xl border border-dashed border-gray-200 bg-[#FAFAFA] p-6 text-center text-xs text-gray-400">
                                    No scheduled classes on {{ $day }}. Enjoy your break!
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-100 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                                <th class="pb-3 pr-4 font-semibold">Time & Duration</th>
                                                <th class="pb-3 px-4 font-semibold">Subject & Code</th>
                                                <th class="pb-3 px-4 font-semibold">Type</th>
                                                <th class="pb-3 px-4 font-semibold">Room / Modality</th>
                                                <th class="pb-3 px-4 font-semibold">Instructor</th>
                                                <th class="pb-3 pl-4 text-right font-semibold">Classroom</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($classes as $item)
                                                <tr class="group hover:bg-indigo-50/30 transition-colors">
                                                    {{-- Time Slot --}}
                                                    <td class="py-4 pr-4 whitespace-nowrap align-middle">
                                                        <div class="flex items-center gap-2">
                                                            <div class="h-2 w-2 rounded-full {{ $item['is_online'] ? 'bg-emerald-500' : 'bg-gold' }}"></div>
                                                            <div>
                                                                <div class="font-bold text-navy text-xs sm:text-sm">
                                                                    {{ $item['time_start'] }} &ndash; {{ $item['time_end'] }}
                                                                </div>
                                                                @if (!empty($item['duration']))
                                                                    <span class="text-[11px] font-medium text-gray-400">
                                                                        {{ $item['duration'] }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>

                                                    {{-- Subject Code & Title --}}
                                                    <td class="py-4 px-4 align-middle">
                                                        <div class="max-w-xs">
                                                            <div class="flex items-center gap-2 mb-1">
                                                                <span class="inline-flex items-center rounded-md bg-[#0A192F] px-2 py-0.5 font-mono text-[11px] font-bold text-gold">
                                                                    {{ $item['subject_code'] }}
                                                                </span>
                                                            </div>
                                                            <a href="{{ route('student.classes.show', $item['subject_id']) }}"
                                                               class="font-serif text-sm font-bold text-navy hover:text-indigo-600 transition line-clamp-2">
                                                                {{ $item['title'] }}
                                                            </a>
                                                        </div>
                                                    </td>

                                                    {{-- Type Badge --}}
                                                    <td class="py-4 px-4 whitespace-nowrap align-middle">
                                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold
                                                            {{ $item['type'] === 'Lec'
                                                                ? 'bg-indigo-50 text-indigo-700 border border-indigo-200'
                                                                : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                                            {{ $item['type'] === 'Lec' ? 'Lecture' : 'Laboratory' }}
                                                        </span>
                                                    </td>

                                                    {{-- Room --}}
                                                    <td class="py-4 px-4 align-middle">
                                                        @if ($item['is_online'])
                                                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                                Online Class
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 border border-line">
                                                                {{ $item['room'] }}
                                                            </span>
                                                        @endif
                                                    </td>

                                                    {{-- Instructor --}}
                                                    <td class="py-4 px-4 align-middle">
                                                        <div class="flex items-center gap-2 text-xs text-gray-700 font-medium">
                                                            <x-icon name="user" class="h-3.5 w-3.5 text-gray-400 shrink-0" />
                                                            <span class="truncate max-w-[180px]">{{ $item['instructor'] ?? '—' }}</span>
                                                        </div>
                                                    </td>

                                                    {{-- Action Link --}}
                                                    <td class="py-4 pl-4 text-right whitespace-nowrap align-middle">
                                                        <a href="{{ route('student.classes.show', $item['subject_id']) }}"
                                                           class="inline-flex items-center gap-1 rounded-xl bg-gray-50 hover:bg-[#0A192F] hover:text-gold px-3 py-1.5 text-xs font-bold text-navy border border-line transition shadow-2xs">
                                                            <span>View Subject</span>
                                                            <span class="text-[10px]">&rarr;</span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>

                {{-- ════════════════════════════════════════════════════════════════ --}}
                {{-- VIEW 2: CLEAN ROOMY SUBJECT CATALOG TABLE                        --}}
                {{-- ════════════════════════════════════════════════════════════════ --}}
                <div x-show="viewMode === 'table'" x-cloak class="rounded-2xl border border-line/80 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h3 class="font-serif text-lg font-bold text-navy">All Enrolled Subjects</h3>
                        <p class="text-xs text-gray-500">Overview of all {{ $subjects->count() }} curriculum subjects for {{ $programName }}.</p>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-line/70">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="bg-[#0A192F] text-left text-xs font-bold uppercase tracking-wider text-gold">
                                    <th class="px-5 py-3.5 whitespace-nowrap">Code</th>
                                    <th class="px-5 py-3.5">Descriptive Title</th>
                                    <th class="px-5 py-3.5">Day & Time Schedule</th>
                                    <th class="px-5 py-3.5">Assigned Room</th>
                                    <th class="px-5 py-3.5">Instructor</th>
                                    <th class="px-5 py-3.5 text-right">Classroom</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($subjects as $subject)
                                    <tr class="hover:bg-indigo-50/30 transition-colors align-top">
                                        {{-- Subject Code --}}
                                        <td class="px-5 py-4 font-mono font-bold text-indigo-700 whitespace-nowrap">
                                            {{ $subject->subject_code }}
                                        </td>

                                        {{-- Title --}}
                                        <td class="px-5 py-4">
                                            <a href="{{ route('student.classes.show', $subject) }}" class="font-serif font-bold text-navy hover:text-indigo-600 transition">
                                                {{ $subject->title }}
                                            </a>
                                            <div class="text-[11px] text-gray-400 mt-0.5">
                                                Block {{ $subject->block ?? '1' }} &bull; Section {{ $subject->section ?? 'A' }}
                                            </div>
                                        </td>

                                        {{-- Schedule Slots --}}
                                        <td class="px-5 py-4 min-w-[220px]">
                                            <div class="space-y-2">
                                                @foreach ($subject->schedules ?? [] as $slot)
                                                    <div class="flex items-center gap-2 text-xs">
                                                        <span class="font-bold text-navy min-w-[20px]">{{ $slot['day'] }}</span>
                                                        <span class="text-gray-600 font-medium">{{ $slot['time_start'] }} &ndash; {{ $slot['time_end'] }}</span>
                                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold
                                                            {{ $slot['type'] === 'Lec' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }}">
                                                            {{ $slot['type'] }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>

                                        {{-- Rooms --}}
                                        <td class="px-5 py-4 text-xs">
                                            <div class="space-y-1.5">
                                                @foreach ($subject->schedules ?? [] as $slot)
                                                    <div class="{{ str_contains($slot['room'] ?? '', 'ONLINE') ? 'text-emerald-700 font-semibold' : 'text-gray-600' }}">
                                                        {{ $slot['room'] ?? '—' }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>

                                        {{-- Instructor --}}
                                        <td class="px-5 py-4 text-xs text-gray-700 font-medium">
                                            {{ $subject->instructor ?? '—' }}
                                        </td>

                                        {{-- Action --}}
                                        <td class="px-5 py-4 text-right whitespace-nowrap">
                                            <a href="{{ route('student.classes.show', $subject) }}"
                                               class="inline-flex items-center gap-1 rounded-xl bg-gray-50 hover:bg-[#0A192F] hover:text-gold px-3 py-1.5 text-xs font-bold text-navy border border-line transition shadow-2xs">
                                                <span>Open</span>
                                                <span class="text-[10px]">&rarr;</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <p class="mt-4 text-xs text-gray-400 text-right">
                {{ $subjects->count() }} subjects ({{ $totalSessions }} weekly sessions) officially registered for <strong>{{ $programName }}</strong>
            </p>
        @endif
    @endif
</x-layouts.app>
