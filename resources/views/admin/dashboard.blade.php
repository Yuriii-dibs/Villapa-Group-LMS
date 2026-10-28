<x-layouts.app>
    <x-slot:title>Administrator Console - UEP LMS</x-slot:title>

    <div class="space-y-8">
        {{-- Dashboard Top Banner --}}
        <div class="bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-serif font-semibold text-navy tracking-tight">School Administrator Console</h1>
                    <span class="inline-flex items-center rounded-full border border-gold/50 bg-gradient-to-r from-[#FAF6ED] to-[#F4ECCF] px-3 py-0.5 text-xs font-serif font-medium text-gold-dark shadow-2xs">
                        School Administrator
                    </span>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <span class="block h-0.5 w-12 bg-gradient-to-r from-gold via-gold-light to-transparent rounded-full"></span>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    System-wide oversight for academic terms, sections, subject offerings, enrollment, and institutional users.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.enrollment.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white border border-gold/40 rounded-xl text-xs sm:text-sm font-semibold hover:border-gold/70 shadow-sm transition">
                    <x-icon name="users" class="h-4 w-4 text-gold" />
                    <span>Enrollment Hub</span>
                </a>
                <a href="{{ route('admin.offerings.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 border border-line bg-white text-navy rounded-xl text-xs sm:text-sm font-semibold hover:border-gold/50 shadow-2xs transition">
                    <x-icon name="book" class="h-4 w-4 text-navy" />
                    <span>Offerings</span>
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 border border-line bg-white text-navy rounded-xl text-xs sm:text-sm font-semibold hover:border-gold/50 shadow-2xs transition">
                    <x-icon name="user" class="h-4 w-4 text-navy" />
                    <span>Users</span>
                </a>
            </div>
        </div>

        {{-- Metrics Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-stat-card label="Total Students" :value="$totalStudents" hint="Active learners" icon="users" />
            <x-stat-card label="Faculty" :value="$totalInstructors" hint="Teaching instructors" icon="user" />
            <x-stat-card label="Course Offerings" :value="$totalOfferings" hint="Active sections" icon="book" />
            <x-stat-card label="Weekly Classes" :value="$totalClassSlots" hint="Scheduled slots" icon="clock" />
            <x-stat-card label="Subjects Catalog" :value="$totalSubjects" hint="Course curriculum" icon="tag" />
            <x-stat-card label="Active Sections" :value="$totalSections" hint="Year level cohorts" icon="folder" />
        </div>

        {{-- Quick Management Control Strip --}}
        <div class="rounded-2xl bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] p-5 text-paper border border-gold/30">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-gold">Current Academic Term</span>
                    <h3 class="font-serif text-lg font-bold text-white">
                        {{ $currentTerm?->name ?? '1st Semester AY 2026–2027' }}
                    </h3>
                    <p class="text-xs text-indigo-200 mt-0.5">
                        {{ $currentTerm ? $currentTerm->start_date->format('M d, Y') . ' – ' . $currentTerm->end_date->format('M d, Y') : 'Active Term' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.terms.index') }}"
                       class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/20 transition">
                        Academic Terms &rarr;
                    </a>
                    <a href="{{ route('admin.sections.index') }}"
                       class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/20 transition">
                        Sections & Cohorts &rarr;
                    </a>
                    <a href="{{ route('admin.subjects.index') }}"
                       class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/20 transition">
                        Subjects Catalog &rarr;
                    </a>
                    <a href="{{ route('admin.announcements.index') }}"
                       class="px-3 py-1.5 rounded-lg bg-gold/20 hover:bg-gold/30 text-xs font-semibold text-gold border border-gold/40 transition">
                        Post Announcement &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Activity Columns --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- 1. Recent Course Offerings --}}
            <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                            <x-icon name="book" class="h-4 w-4" />
                        </span>
                        <h2 class="font-serif text-base font-bold text-navy">Course Offerings</h2>
                    </div>
                    <a href="{{ route('admin.offerings.index') }}" class="text-xs font-semibold text-indigo-700 hover:text-navy">
                        All &rarr;
                    </a>
                </div>

                @if ($recentOfferings->isEmpty())
                    <p class="text-xs text-gray-400 italic text-center py-6">No offerings created yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($recentOfferings as $off)
                            <div class="p-3 rounded-xl bg-gray-50 border border-line/60 text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-navy">{{ $off->subject?->subject_code }}</span>
                                    <span class="rounded bg-indigo-50 border border-indigo-200 px-2 py-0.2 font-semibold text-indigo-700 text-[10px]">
                                        {{ $off->section?->name }}
                                    </span>
                                </div>
                                <div class="font-serif font-medium text-navy truncate">
                                    {{ $off->subject?->title }}
                                </div>
                                <div class="text-[11px] text-gray-500">
                                    Faculty: {{ $off->instructor?->name ?: ($off->instructor_name ?: 'TBA') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 2. Recent User Registrations --}}
            <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                            <x-icon name="users" class="h-4 w-4" />
                        </span>
                        <h2 class="font-serif text-base font-bold text-navy">Recent Users</h2>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-indigo-700 hover:text-navy">
                        Manage &rarr;
                    </a>
                </div>

                @if ($recentUsers->isEmpty())
                    <p class="text-xs text-gray-400 italic text-center py-6">No users found.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($recentUsers as $u)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-line/60 text-xs">
                                <div>
                                    <div class="font-bold text-navy">{{ $u->name }}</div>
                                    <div class="text-[11px] text-gray-500 font-mono">{{ $u->email }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold uppercase
                                        {{ $u->hasRole('admin') ? 'bg-purple-50 text-purple-800' : ($u->hasRole('instructor') ? 'bg-amber-50 text-amber-800' : 'bg-blue-50 text-blue-800') }}">
                                        {{ $u->getPrimaryRole() }}
                                    </span>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $u->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 3. Institutional Announcements --}}
            <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                            <x-icon name="chat" class="h-4 w-4" />
                        </span>
                        <h2 class="font-serif text-base font-bold text-navy">Announcements</h2>
                    </div>
                    <a href="{{ route('admin.announcements.index') }}" class="text-xs font-semibold text-indigo-700 hover:text-navy">
                        All &rarr;
                    </a>
                </div>

                @if ($recentAnnouncements->isEmpty())
                    <p class="text-xs text-gray-400 italic text-center py-6">No announcements published.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($recentAnnouncements as $ann)
                            <div class="p-3 rounded-xl bg-gray-50 border border-line/60 text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="rounded bg-indigo-50 border border-indigo-200 px-2 py-0.2 font-bold text-indigo-700 text-[10px]">
                                        {{ strtoupper($ann->target_type) }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">{{ $ann->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="font-serif font-bold text-navy line-clamp-1">
                                    {{ $ann->title }}
                                </div>
                                <p class="text-gray-500 line-clamp-2 text-[11px]">
                                    {{ $ann->content }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-layouts.app>
