<x-layouts.app>
    <x-slot:title>Student Dashboard – UEP LMS</x-slot:title>

    <x-page-header
        title="Welcome back, {{ Str::before(auth()->user()->name, ' ') }}"
        :badge="$programCode ? 'Program: ' . $programCode : 'Student'"
        subtitle="University of Eastern Pangasinan — Academic Student Portal"
    >
        <x-slot:actions>
            <a href="{{ route('schedule.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gold/50 bg-[#0A192F] px-4 py-2 text-xs sm:text-sm font-semibold text-gold hover:bg-[#14365D] hover:text-white transition shadow-sm">
                <x-icon name="calendar" class="h-4 w-4" /> My Schedule
            </a>
            <a href="{{ route('student.assignments.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-navy hover:bg-gray-50 transition shadow-2xs">
                <x-icon name="inbox" class="h-4 w-4" /> View All Assignments
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-8">

        {{-- ── 1. ACADEMIC OVERVIEW CARDS ────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card label="Enrolled Subjects" :value="$enrolledCount" hint="Current active term" icon="book" />
            <x-stat-card label="Upcoming Classes" :value="$upcomingClasses->count()" hint="Next 7 days schedule" icon="video" />
            <x-stat-card label="Passed Submissions" :value="$submittedCount" hint="Activities completed" icon="check" />
        </div>

        @if ($enrolledCount === 0)
            <div class="rounded-2xl border border-amber-300 bg-amber-50/70 p-5 text-amber-900 flex items-start gap-4 shadow-2xs">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 border border-amber-300">
                    <x-icon name="book" class="h-5 w-5" />
                </span>
                <div>
                    <h3 class="font-serif text-base font-bold text-amber-950">Awaiting Subject Enrollment</h3>
                    <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                        You are not currently enrolled in any subjects. An administrator will enroll you into your section or subjects. Once enrolled, your classes, schedule, assignments, and subject group chats will appear here automatically.
                    </p>
                </div>
            </div>
        @endif

        {{-- ── 2. CAMPUS & CLASS ANNOUNCEMENTS ─────────────────────────────── --}}
        <section x-data="{ activeScope: 'all' }" class="rounded-2xl border border-line/80 bg-white p-6 shadow-[0_2px_14px_rgba(10,25,47,0.03)] space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-line/60">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gold/15 text-gold border border-gold/40 shadow-2xs">
                        <x-icon name="chat" class="h-4.5 w-4.5" />
                    </span>
                    <div>
                        <h2 class="font-serif text-lg sm:text-xl font-bold text-navy">Campus &amp; Class Announcements</h2>
                        <p class="text-xs text-gray-500">Official bulletins from school administration, your section, and subject professors.</p>
                    </div>
                </div>

                {{-- Filter Tabs --}}
                <div class="flex items-center gap-1.5 p-1 bg-[#FAF8F2] border border-line/80 rounded-xl text-xs font-semibold overflow-x-auto">
                    <button type="button"
                            @click="activeScope = 'all'"
                            :class="activeScope === 'all' ? 'bg-[#0A192F] text-gold shadow-2xs' : 'text-gray-600 hover:text-navy'"
                            class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                        All ({{ $announcements->count() }})
                    </button>
                    <button type="button"
                            @click="activeScope = 'global'"
                            :class="activeScope === 'global' ? 'bg-[#0A192F] text-gold shadow-2xs' : 'text-gray-600 hover:text-navy'"
                            class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                        Campus Wide
                    </button>
                    @if ($user->section)
                        <button type="button"
                                @click="activeScope = 'section'"
                                :class="activeScope === 'section' ? 'bg-[#0A192F] text-gold shadow-2xs' : 'text-gray-600 hover:text-navy'"
                                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                            Section ({{ $user->section->name }})
                        </button>
                    @endif
                    <button type="button"
                            @click="activeScope = 'offering'"
                            :class="activeScope === 'offering' ? 'bg-[#0A192F] text-gold shadow-2xs' : 'text-gray-600 hover:text-navy'"
                            class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                        Class Notices
                    </button>
                </div>
            </div>

            @if ($announcements->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 bg-[#FAFAFA] p-8 text-center text-xs text-gray-400">
                    No active announcements at this time. All campus updates will appear here.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($announcements as $notice)
                        @php
                            $scopeType = $notice->target_type;
                            $isPinned = $notice->is_pinned;
                            $isUrgent = ($notice->priority === 'urgent');
                        @endphp
                        <div x-show="activeScope === 'all' || activeScope === '{{ $scopeType }}'"
                             x-data="{ expanded: false }"
                             class="flex flex-col justify-between rounded-xl border p-5 transition shadow-2xs
                                    {{ $isPinned
                                        ? 'border-gold/60 bg-[#FFFEFA] ring-1 ring-gold/25'
                                        : ($isUrgent
                                            ? 'border-rose-300 bg-rose-50/20'
                                            : 'border-line/80 bg-[#FAF8F5] hover:border-gold/50') }}">
                            <div>
                                {{-- Header: Badges & Timestamp --}}
                                <div class="flex items-center justify-between gap-2 mb-2.5">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if ($isPinned)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-gold/15 px-2 py-0.5 text-[10px] font-bold text-gold border border-gold/40">
                                                PINNED
                                            </span>
                                        @endif

                                        @if ($isUrgent)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 text-rose-800 px-2 py-0.5 text-[10px] font-bold border border-rose-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                URGENT
                                            </span>
                                        @endif

                                        @if ($scopeType === 'global')
                                            <span class="inline-flex items-center rounded-md bg-indigo-50 text-indigo-700 px-2 py-0.5 text-[10px] font-bold border border-indigo-200">
                                                CAMPUS WIDE
                                            </span>
                                        @elseif ($scopeType === 'section')
                                            <span class="inline-flex items-center rounded-md bg-emerald-50 text-emerald-800 px-2 py-0.5 text-[10px] font-bold border border-emerald-200">
                                                SECTION {{ $notice->section?->name ?? 'COHORT' }}
                                            </span>
                                        @elseif ($scopeType === 'offering')
                                            <span class="inline-flex items-center rounded-md bg-gold/15 text-navy px-2 py-0.5 text-[10px] font-bold border border-gold/30">
                                                {{ $notice->subject?->subject_code ?? 'CLASS' }} &bull; {{ $notice->subject?->title ?? 'Class Notice' }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-gray-100 text-gray-700 px-2 py-0.5 text-[10px] font-bold uppercase">
                                                {{ ucfirst($scopeType) }}
                                            </span>
                                        @endif
                                    </div>

                                    <span class="text-[11px] text-gray-400 font-mono shrink-0" title="{{ $notice->created_at->format('M d, Y • g:i A') }}">
                                        {{ $notice->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                {{-- Title --}}
                                <h3 class="font-serif text-base sm:text-lg font-bold text-navy leading-snug">
                                    {{ $notice->title }}
                                </h3>

                                {{-- Content with Read More toggle --}}
                                <div class="mt-2.5 text-xs text-gray-600 leading-relaxed whitespace-pre-line">
                                    <div :class="expanded ? '' : 'line-clamp-3'">
                                        {{ $notice->content }}
                                    </div>
                                    @if (strlen($notice->content) > 160)
                                        <button type="button"
                                                @click="expanded = !expanded"
                                                class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-navy transition">
                                            <span x-text="expanded ? 'Show less &uarr;' : 'Read full notice &darr;'"></span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Footer: Author & Department --}}
                            <div class="mt-4 pt-3 border-t border-line/60 flex items-center justify-between text-[11px] text-gray-500">
                                <div class="flex items-center gap-1.5 truncate">
                                    <span>Posted by:</span>
                                    <strong class="text-navy font-semibold truncate">{{ $notice->author?->name ?? 'Administration' }}</strong>
                                    <span class="text-[10px] text-gray-400 font-medium shrink-0">({{ $notice->author?->getPrimaryRoleDisplayName() ?? 'School Administrator' }})</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-mono shrink-0 ml-2">
                                    {{ $notice->created_at->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ── 3. TWO-COLUMN: UPCOMING CLASSES & ASSIGNMENTS DUE SOON ─────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

            {{-- 3A. UPCOMING CLASSES (NEXT 7 DAYS) --}}
            <section class="rounded-2xl border border-line/80 bg-white p-6 shadow-[0_2px_14px_rgba(10,25,47,0.03)]">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-line/60">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-50 text-rose-700 border border-rose-200">
                            <x-icon name="video" class="h-4 w-4" />
                        </span>
                        <div>
                            <h2 class="font-serif text-lg font-bold text-navy">Upcoming Classes (Next 7 Days)</h2>
                            <p class="text-xs text-gray-500">Live lecture & laboratory timetable with Google Meet links.</p>
                        </div>
                    </div>
                    <a href="{{ route('schedule.index') }}" class="text-xs font-bold text-indigo-600 hover:text-navy transition">
                        Full Schedule &rarr;
                    </a>
                </div>

                @if ($upcomingClasses->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-200 bg-[#FAFAFA] p-8 text-center text-xs text-gray-400">
                        No class sessions scheduled in the upcoming week.
                    </div>
                @else
                    <div class="space-y-3.5 max-h-[580px] overflow-y-auto pr-1">
                        @foreach ($upcomingClasses as $class)
                            <div class="rounded-xl border {{ $class['is_live'] ? 'border-rose-300 bg-rose-50/20 ring-1 ring-rose-200' : 'border-line/80 bg-[#FFFEFA]' }} p-4 transition hover:border-gold/50 shadow-2xs">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            <span class="inline-flex items-center rounded-md bg-[#0A192F] px-2 py-0.5 font-mono text-[11px] font-bold text-gold">
                                                {{ $class['subject_code'] }}
                                            </span>
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold {{ $class['type'] === 'Lec' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ $class['type'] }}
                                            </span>
                                            <span class="text-xs font-bold text-navy">
                                                {{ $class['date_formatted'] }} &bull; {{ $class['time_start'] }} &ndash; {{ $class['time_end'] }}
                                            </span>
                                        </div>

                                        <h4 class="font-serif text-sm font-bold text-navy truncate">
                                            {{ $class['subject_title'] }}
                                        </h4>

                                        <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-500">
                                            <span class="truncate">Prof. {{ $class['instructor'] }}</span>
                                            <span>&bull;</span>
                                            <span class="{{ $class['is_online'] ? 'text-emerald-700 font-semibold' : 'text-gray-600' }}">
                                                {{ $class['room'] }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Google Meet Button (Visible 15 min before start) --}}
                                    <div class="shrink-0 flex flex-col items-end gap-1">
                                        @if ($class['is_live'] || $class['can_join'])
                                            <a href="{{ $class['meet_link'] }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition animate-pulse">
                                                <x-icon name="video" class="h-3.5 w-3.5 text-white" />
                                                <span>Join Google Meet</span>
                                            </a>
                                            <span class="text-[10px] font-bold text-emerald-700">Open Now</span>
                                        @elseif ($class['is_upcoming'])
                                            <button type="button" disabled
                                                    title="Opens 15 minutes before class starts"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 px-3.5 py-2 text-xs font-semibold text-gray-400 cursor-not-allowed border border-gray-200">
                                                <x-icon name="clock" class="h-3.5 w-3.5 text-gray-400" />
                                                <span>Opens 15m Prior</span>
                                            </button>
                                            <span class="text-[10px] text-gray-400">Scheduled</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 3B. ASSIGNMENTS DUE SOON --}}
            <section class="rounded-2xl border border-line/80 bg-white p-6 shadow-[0_2px_14px_rgba(10,25,47,0.03)]">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-line/60">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-800 border border-amber-200">
                            <x-icon name="inbox" class="h-4 w-4" />
                        </span>
                        <div>
                            <h2 class="font-serif text-lg font-bold text-navy">Assignments Due Soon</h2>
                            <p class="text-xs text-gray-500">Upcoming activities, case studies, and lab requirements.</p>
                        </div>
                    </div>
                    <a href="{{ route('student.assignments.index') }}" class="text-xs font-bold text-indigo-600 hover:text-navy transition">
                        View All &rarr;
                    </a>
                </div>

                @if ($assignmentsDueSoon->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-200 bg-[#FAFAFA] p-8 text-center text-xs text-gray-400">
                        No pending assignments due soon. All coursework is up to date!
                    </div>
                @else
                    <div class="space-y-3.5 max-h-[580px] overflow-y-auto pr-1">
                        @foreach ($assignmentsDueSoon as $assignment)
                            @php
                                $submission = $assignment->submissions->first();
                                $isSubmitted = $submission !== null;
                            @endphp
                            <div class="rounded-xl border {{ $isSubmitted ? 'border-emerald-200 bg-emerald-50/20' : 'border-line/80 bg-[#FFFEFA]' }} p-4 transition hover:border-gold/50 shadow-2xs">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            <span class="inline-flex items-center rounded-md bg-[#0A192F] px-2 py-0.5 font-mono text-[11px] font-bold text-gold">
                                                {{ $assignment->subject->subject_code }}
                                            </span>
                                            @if ($isSubmitted)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-200">
                                                    <x-icon name="check" class="h-3 w-3" /> Submitted
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 border border-amber-200">
                                                    <x-icon name="clock" class="h-3 w-3" /> Pending
                                                </span>
                                            @endif
                                            <span class="text-xs font-semibold text-gray-500">
                                                {{ $assignment->total_points }} pts
                                            </span>
                                        </div>

                                        <h4 class="font-serif text-sm font-bold text-navy">
                                            {{ $assignment->title }}
                                        </h4>

                                        <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                                            <span>Subject: {{ $assignment->subject->title }}</span>
                                        </div>

                                        @if ($assignment->due_date)
                                            <div class="mt-1 text-[11px] font-medium {{ $assignment->due_date->isPast() ? 'text-red-600' : 'text-amber-700' }}">
                                                Due: {{ $assignment->due_date->format('M d, Y – g:i A') }} ({{ $assignment->due_date->diffForHumans() }})
                                            </div>
                                        @endif
                                    </div>

                                    <div class="shrink-0">
                                        <a href="{{ route('student.assignments.show', $assignment) }}"
                                           class="inline-flex items-center gap-1 rounded-xl bg-gray-50 hover:bg-[#0A192F] hover:text-gold px-3 py-1.5 text-xs font-bold text-navy border border-line transition shadow-2xs">
                                            <span>{{ $isSubmitted ? 'View Submission' : 'Submit Output' }}</span>
                                            <span class="text-[10px]">&rarr;</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

        </div>

    </div>
</x-layouts.app>
