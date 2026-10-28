<x-layouts.app>
    <x-slot:title>Faculty Dashboard - UEP LMS</x-slot:title>

    <div class="space-y-8">
        {{-- Dashboard Header --}}
        <div class="bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-serif font-semibold text-navy tracking-tight">Faculty Dashboard</h1>
                    <span class="inline-flex items-center rounded-full border border-gold/50 bg-gradient-to-r from-[#FAF6ED] to-[#F4ECCF] px-3 py-0.5 text-xs font-serif font-medium text-gold-dark shadow-2xs">
                        Instructor
                    </span>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <span class="block h-0.5 w-12 bg-gradient-to-r from-gold via-gold-light to-transparent rounded-full"></span>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Welcome back, {{ $user->name }}. Review today's teaching schedule, student submissions, and announcements.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('instructor.assignments.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white border border-gold/40 rounded-xl text-xs sm:text-sm font-semibold hover:border-gold/70 shadow-sm transition">
                    <x-icon name="plus" class="h-4 w-4 text-gold" />
                    <span>New Assignment</span>
                </a>
                <a href="{{ route('instructor.offerings.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#FAF8F2] text-navy border border-line rounded-xl text-xs sm:text-sm font-semibold hover:border-gold/50 transition">
                    <x-icon name="book" class="h-4 w-4 text-navy" />
                    <span>My Classes</span>
                </a>
            </div>
        </div>

        {{-- Metrics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-stat-card label="Today's Classes" :value="$todaysClasses->count()" hint="{{ $todayName }} teaching slots" icon="clock" />
            <x-stat-card label="Assignments to Review" :value="$totalPendingReviewCount" hint="Submissions awaiting grading" icon="inbox" />
            <x-stat-card label="Total Students" :value="$totalStudents" hint="Across all active offerings" icon="users" />
            <x-stat-card label="My Offerings" :value="$totalOfferings" hint="Current academic term" icon="book" />
        </div>

        {{-- Today's Classes & Assignments Needing Review Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Today's Classes (7 cols) --}}
            <div class="lg:col-span-7 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                            <x-icon name="clock" class="h-4 w-4" />
                        </span>
                        <h2 class="font-serif text-lg font-bold text-navy tracking-tight">Today's Classes ({{ $todayName }})</h2>
                    </div>
                    <a href="{{ route('schedule.index') }}" class="text-xs font-semibold text-indigo-700 hover:text-navy transition">
                        Full Schedule &rarr;
                    </a>
                </div>

                @if ($todaysClasses->isEmpty())
                    <div class="p-8 text-center text-gray-500 bg-[#FFFEFA] rounded-2xl border border-dashed border-line shadow-2xs">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-400 mb-2">
                            <x-icon name="calendar" class="h-5 w-5" />
                        </div>
                        <p class="text-sm font-semibold text-navy">No scheduled classes for today</p>
                        <p class="text-xs text-gray-400 mt-1">Enjoy your prep time or review incoming assignments.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($todaysClasses as $slot)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-[#FFFEFA] border border-line/90 shadow-2xs hover:border-gold/50 transition">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-md bg-[#0A192F] px-2 py-0.5 font-mono text-[11px] font-bold text-gold">
                                            {{ $slot['subject_code'] }}
                                        </span>
                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">
                                            {{ $slot['section'] }}
                                        </span>
                                        @if ($slot['is_online'])
                                            <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 text-[11px] font-bold">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Online
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="font-serif text-sm font-bold text-navy">
                                        {{ $slot['title'] }}
                                    </h3>
                                    <div class="flex items-center gap-4 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <x-icon name="clock" class="h-3.5 w-3.5 text-gray-400" />
                                            {{ $slot['time_start'] }} &ndash; {{ $slot['time_end'] }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <x-icon name="tag" class="h-3.5 w-3.5 text-gray-400" />
                                            {{ $slot['room'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="shrink-0 flex items-center gap-2">
                                    @if (!empty($slot['meet_link']))
                                        <a href="{{ $slot['meet_link'] }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                                            <x-icon name="video" class="h-3.5 w-3.5" />
                                            <span>Join Meet</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('instructor.offerings.show', $slot['offering_id']) }}"
                                       class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-gray-50 hover:bg-[#0A192F] hover:text-gold text-navy text-xs font-semibold border border-line transition">
                                        <span>Details</span>
                                        <span class="text-[10px]">&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Announcements Card --}}
                <div class="mt-8 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                                <x-icon name="chat" class="h-4 w-4" />
                            </span>
                            <h2 class="font-serif text-lg font-bold text-navy tracking-tight">Announcements</h2>
                        </div>
                    </div>

                    {{-- Post Announcement Form Modal / Card --}}
                    <div class="rounded-2xl bg-[#FFFEFA] border border-line p-5 shadow-2xs">
                        <form method="POST" action="{{ route('instructor.announcements.store') }}" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Target Offering</label>
                                    <select name="subject_offering_id" required class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                        <option value="">Select Class Offering</option>
                                        @foreach ($offerings as $offering)
                                            <option value="{{ $offering->id }}">
                                                {{ $offering->subject?->subject_code }} ({{ $offering->section?->name ?: 'Section' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Announcement Title</label>
                                    <input type="text" name="title" required placeholder="e.g. Schedule update for next laboratory session"
                                           class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Message Content</label>
                                <textarea name="content" rows="2" required placeholder="Write your announcement details for students..."
                                          class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold"></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0A192F] hover:bg-[#14365D] text-gold text-xs font-bold border border-gold/40 transition">
                                    <x-icon name="check" class="h-3.5 w-3.5" />
                                    <span>Post Announcement</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Recent Announcements List --}}
                    @if ($announcements->isEmpty())
                        <div class="p-6 text-center text-gray-400 bg-gray-50 rounded-2xl border border-line text-xs">
                            No announcements posted yet.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($announcements as $announcement)
                                <div class="p-4 rounded-xl bg-white border border-line shadow-2xs space-y-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            @if ($announcement->target_type === 'global')
                                                <span class="rounded bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-[10px] font-bold text-indigo-700">
                                                    Global
                                                </span>
                                            @elseif ($announcement->subjectOffering)
                                                <span class="rounded bg-gold/15 border border-gold/40 px-2 py-0.5 text-[10px] font-bold text-gold-dark">
                                                    {{ $announcement->subjectOffering->subject?->subject_code }} &bull; {{ $announcement->subjectOffering->section?->name }}
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-gray-400 font-medium">
                                                {{ $announcement->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <h4 class="font-serif text-sm font-bold text-navy">{{ $announcement->title }}</h4>
                                    <p class="text-xs text-gray-600 leading-relaxed">{{ $announcement->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Assignments Needing Review (5 cols) --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                            <x-icon name="inbox" class="h-4 w-4" />
                        </span>
                        <h2 class="font-serif text-lg font-bold text-navy tracking-tight">Needs Review</h2>
                    </div>
                    <a href="{{ route('instructor.assignments.index') }}" class="text-xs font-semibold text-indigo-700 hover:text-navy transition">
                        All ({{ $totalPendingReviewCount }}) &rarr;
                    </a>
                </div>

                @if ($assignmentsNeedingReview->isEmpty())
                    <div class="p-8 text-center text-gray-500 bg-[#FFFEFA] rounded-2xl border border-dashed border-line shadow-2xs">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 mb-2">
                            <x-icon name="check" class="h-5 w-5" />
                        </div>
                        <p class="text-sm font-semibold text-navy">All caught up!</p>
                        <p class="text-xs text-gray-400 mt-1">There are no pending submissions awaiting grading.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($assignmentsNeedingReview as $sub)
                            <div class="p-4 rounded-2xl bg-[#FFFEFA] border border-line/90 shadow-2xs hover:border-gold/50 transition space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-md bg-[#0A192F] px-2 py-0.5 font-mono text-[10px] font-bold text-gold">
                                        {{ $sub->assignment->subject?->subject_code ?? 'SUBJ' }}
                                    </span>
                                    <span class="text-[11px] text-amber-700 font-bold bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                                        Pending Grade
                                    </span>
                                </div>

                                <div>
                                    <h4 class="font-serif text-xs sm:text-sm font-bold text-navy line-clamp-1">
                                        {{ $sub->assignment->title }}
                                    </h4>
                                    <p class="text-xs text-gray-600 mt-0.5">
                                        Submitted by <span class="font-semibold text-navy">{{ $sub->user->name }}</span>
                                    </p>
                                </div>

                                <div class="pt-2 border-t border-line/60 flex items-center justify-between text-xs">
                                    <span class="text-[11px] text-gray-400">
                                        {{ $sub->submitted_at ? $sub->submitted_at->diffForHumans() : 'Recently' }}
                                    </span>
                                    <a href="{{ route('instructor.assignments.submissions', $sub->subject_assignment_id) }}"
                                       class="inline-flex items-center gap-1 font-bold text-indigo-700 hover:text-navy">
                                        <span>Grade Submission</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Quick Offerings Box --}}
                <div class="mt-8 rounded-2xl bg-gradient-to-br from-[#071321] to-[#0A192F] p-5 text-paper border border-gold/30 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-serif text-sm font-bold text-white">Offerings Taught</h3>
                        <a href="{{ route('instructor.offerings.index') }}" class="text-xs text-gold hover:underline">
                            View All &rarr;
                        </a>
                    </div>
                    <div class="space-y-2">
                        @foreach ($offerings->take(4) as $off)
                            <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-white/5 border border-white/10">
                                <div>
                                    <span class="font-mono font-bold text-gold">{{ $off->subject?->subject_code }}</span>
                                    <span class="text-gray-300 ml-1.5">{{ $off->section?->name }}</span>
                                </div>
                                <span class="text-[11px] text-gray-400">
                                    {{ $off->students->count() }} students
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
