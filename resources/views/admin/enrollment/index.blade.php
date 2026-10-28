<x-layouts.app>
    <x-slot:title>Enrollment Management – Admin Console</x-slot:title>

    <div class="space-y-8">
        <x-page-header
            title="Enrollment Management"
            subtitle="Pick and enroll students into cohort sections or customize individual offerings for irregular students."
        >
            <x-slot:actions>
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 border border-line rounded-xl text-xs sm:text-sm font-semibold text-navy bg-white hover:border-gold/50 transition">
                    &larr; Dashboard
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Alerts --}}
        @if (session('warning'))
            <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-xs font-semibold text-amber-900 shadow-2xs">
                <div class="flex items-center gap-2 mb-1">
                    <x-icon name="clock" class="h-4 w-4 text-amber-600" />
                    <span class="font-bold">Schedule Conflict Warning:</span>
                </div>
                <p>{{ session('warning') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-semibold text-rose-900 shadow-2xs">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Panel 1: Pick Students to Enroll in Section (6 cols) --}}
            <div class="lg:col-span-6 space-y-4">
                <div x-data="{
                    searchTerm: '',
                    filterMode: 'all',
                    selectAll() {
                        const cbs = document.querySelectorAll('.student-checkbox');
                        cbs.forEach(cb => {
                            if (cb.closest('.student-row').style.display !== 'none') {
                                cb.checked = true;
                            }
                        });
                    },
                    deselectAll() {
                        const cbs = document.querySelectorAll('.student-checkbox');
                        cbs.forEach(cb => cb.checked = false);
                    }
                }" class="rounded-2xl border border-line bg-[#FFFEFA] p-6 shadow-2xs space-y-5">
                    
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                            <x-icon name="users" class="h-4 w-4" />
                        </span>
                        <div>
                            <h2 class="font-serif text-base font-bold text-navy">Section Enrollment & Student Picker</h2>
                            <p class="text-[11px] text-gray-500">Pick which students to assign and enroll into a section's offerings.</p>
                        </div>
                    </div>

                    {{-- Step 1: Select Section & Term --}}
                    <form method="GET" action="{{ route('admin.enrollment.index') }}" class="space-y-3 pb-3 border-b border-line">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Target Academic Term</label>
                                <select name="academic_term_id" class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                    @foreach ($terms as $t)
                                        <option value="{{ $t->id }}" {{ $t->is_current ? 'selected' : '' }}>
                                            {{ $t->name }} {{ $t->is_current ? '(Active)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Select Section Cohort *</label>
                                <select name="section_id" onchange="this.form.submit()" class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold font-bold">
                                    <option value="">-- Choose Section --</option>
                                    @foreach ($sections as $s)
                                        <option value="{{ $s->id }}" {{ $selectedSectionId == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }} (Year {{ $s->year_level }} &bull; {{ $s->students_count }} enrolled)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    @if ($selectedSection)
                        {{-- Step 2: Student Checklist & Enrollment Submission Form --}}
                        <form method="POST" action="{{ route('admin.enrollment.enroll-section') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="academic_term_id" value="{{ $currentTerm?->id }}">
                            <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-serif text-sm font-bold text-navy">
                                        Pick Students for Section {{ $selectedSection->name }}
                                    </h3>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="selectAll()" class="text-[11px] font-bold text-indigo-700 hover:underline">
                                            Select All
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" @click="deselectAll()" class="text-[11px] font-bold text-gray-500 hover:underline">
                                            Deselect
                                        </button>
                                    </div>
                                </div>

                                {{-- Search & Filter Bar --}}
                                <div class="relative">
                                    <input type="text" x-model="searchTerm" placeholder="Search student by name or email..."
                                           class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                                </div>
                            </div>

                            {{-- Student Checklist Box --}}
                            <div class="space-y-1.5 max-h-[380px] overflow-y-auto pr-1">
                                @forelse ($students as $stu)
                                    @php
                                        $inThisSection = ($stu->section_id === $selectedSection->id);
                                    @endphp
                                    <label class="student-row flex items-center justify-between gap-3 p-2.5 rounded-xl border {{ $inThisSection ? 'border-gold/50 bg-gold/5' : 'border-line/70 bg-gray-50/50' }} hover:border-gold/40 transition cursor-pointer"
                                           x-show="searchTerm === '' || '{{ strtolower($stu->name) }} {{ strtolower($stu->email) }}'.includes(searchTerm.toLowerCase())">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <input type="checkbox" name="student_ids[]" value="{{ $stu->id }}"
                                                   {{ $inThisSection ? 'checked' : '' }}
                                                   class="student-checkbox rounded border-line text-navy focus:ring-gold h-4 w-4" />
                                            <div class="min-w-0 text-xs">
                                                <div class="font-bold text-navy truncate">{{ $stu->name }}</div>
                                                <div class="text-[11px] text-gray-400 font-mono truncate">{{ $stu->email }}</div>
                                            </div>
                                        </div>

                                        <div class="text-right shrink-0">
                                            @if ($inThisSection)
                                                <span class="inline-flex items-center rounded-md bg-gold/20 px-2 py-0.5 text-[10px] font-bold text-gold-dark border border-gold/30">
                                                    Current Section
                                                </span>
                                            @elseif ($stu->section)
                                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">
                                                    {{ $stu->section->name }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                                                    Unassigned
                                                </span>
                                            @endif
                                        </div>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-400 italic text-center py-6">No students found.</p>
                                @endforelse
                            </div>

                            <div class="pt-3 border-t border-line flex items-center justify-between">
                                <span class="text-xs text-gray-500 font-medium">
                                    {{ $selectedSection->offerings->count() }} active offerings in this section
                                </span>

                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white text-xs font-bold border border-gold/40 hover:border-gold/70 shadow-sm transition">
                                    <x-icon name="check" class="h-4 w-4 text-gold" />
                                    <span>Enroll Selected Students</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="p-8 text-center text-gray-400 bg-gray-50 rounded-2xl border border-dashed border-line text-xs">
                            Select a section cohort above to view its student roster and pick students to enroll into this section and its offerings.
                        </div>
                    @endif

                </div>

                {{-- Sections Summary Roster Box --}}
                <div class="rounded-2xl border border-line bg-[#FFFEFA] p-5 shadow-2xs space-y-3">
                    <h3 class="font-serif text-sm font-bold text-navy">Section Cohorts Roster</h3>
                    <div class="space-y-2">
                        @foreach ($sections as $sec)
                            <a href="{{ route('admin.enrollment.index', ['section_id' => $sec->id]) }}"
                               class="flex items-center justify-between text-xs p-2.5 rounded-xl {{ $selectedSectionId == $sec->id ? 'bg-[#0A192F] text-paper border border-gold/40' : 'bg-gray-50 text-navy border border-line/60 hover:bg-gray-100' }} transition">
                                <div>
                                    <span class="font-mono font-bold {{ $selectedSectionId == $sec->id ? 'text-gold' : 'text-navy' }}">{{ $sec->name }}</span>
                                    <span class="ml-1.5 {{ $selectedSectionId == $sec->id ? 'text-gray-300' : 'text-gray-500' }}">Year {{ $sec->year_level }} &bull; {{ $sec->program }}</span>
                                </div>
                                <span class="rounded px-2 py-0.5 font-bold text-[10px] {{ $selectedSectionId == $sec->id ? 'bg-gold/20 text-gold border border-gold/30' : 'bg-indigo-50 border border-indigo-200 text-indigo-700' }}">
                                    {{ $sec->students_count }} students &rarr;
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Panel 2: Irregular Student Offering Picker (6 cols) --}}
            <div class="lg:col-span-6 space-y-4">
                <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 shadow-2xs space-y-5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                            <x-icon name="user" class="h-4 w-4" />
                        </span>
                        <div>
                            <h2 class="font-serif text-base font-bold text-navy">Irregular Student Offering Picker</h2>
                            <p class="text-[11px] text-gray-500">Pick individual subject offerings with automatic schedule conflict detection.</p>
                        </div>
                    </div>

                    {{-- Student Selector Form --}}
                    <form method="GET" action="{{ route('admin.enrollment.index') }}" class="flex items-end gap-3">
                        @if ($selectedSectionId)
                            <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
                        @endif
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Select Student</label>
                            <select name="student_id" onchange="this.form.submit()" class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold font-bold">
                                <option value="">-- Choose a Student to Customize Offerings --</option>
                                @foreach ($students as $stu)
                                    <option value="{{ $stu->id }}" {{ $selectedStudentId == $stu->id ? 'selected' : '' }}>
                                        {{ $stu->name }} ({{ $stu->section?->name ?: 'No section' }} &bull; {{ $stu->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    @if ($selectedStudent)
                        {{-- Enrollment Picker Form --}}
                        <form method="POST" action="{{ route('admin.enrollment.enroll-student') }}" class="space-y-4 pt-4 border-t border-line">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $selectedStudent->id }}">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-xl bg-gray-50 border border-line/60 text-xs">
                                <div>
                                    <div class="font-bold text-navy text-sm">{{ $selectedStudent->name }}</div>
                                    <div class="text-gray-500 font-mono text-[11px]">{{ $selectedStudent->email }}</div>
                                    <div class="text-gray-400 text-[10px] mt-0.5">Program: <strong>{{ $selectedStudent->program?->name ?? 'None Assigned' }}</strong></div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-navy mb-1">Assign Section Cohort</label>
                                    <select name="section_id" class="w-full text-xs rounded-xl border border-line bg-white px-2.5 py-1.5 text-navy focus:border-gold font-semibold">
                                        <option value="">-- No Section (Irregular) --</option>
                                        @foreach ($sections as $sec)
                                            <option value="{{ $sec->id }}" {{ $selectedStudent->section_id == $sec->id ? 'selected' : '' }}>
                                                Section {{ $sec->name }} (Year {{ $sec->year_level }} &bull; {{ $sec->program }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <h3 class="font-serif text-sm font-bold text-navy">
                                    Offerings for {{ $currentTerm?->name ?? 'Current Term' }}
                                </h3>
                                <span class="text-[11px] text-gray-500">
                                    {{ count($enrolledOfferingIds) }} currently enrolled
                                </span>
                            </div>

                            <div class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
                                @forelse ($availableOfferings as $off)
                                    @php
                                        $isEnrolled = in_array($off->id, old('offering_ids', $enrolledOfferingIds));
                                    @endphp
                                    <label class="flex items-start gap-3 p-3 rounded-xl border {{ $isEnrolled ? 'border-gold/60 bg-gold/5' : 'border-line/70 bg-gray-50/50' }} hover:border-gold/40 transition cursor-pointer">
                                        <input type="checkbox" name="offering_ids[]" value="{{ $off->id }}" {{ $isEnrolled ? 'checked' : '' }}
                                               class="rounded border-line text-navy focus:ring-gold mt-1" />
                                        <div class="flex-1 text-xs">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono font-bold text-navy">{{ $off->subject?->subject_code }}</span>
                                                    <span class="rounded bg-gray-100 px-1.5 py-0.2 text-[10px] text-gray-600 font-semibold">
                                                        {{ $off->section?->name ?: 'General' }}
                                                    </span>
                                                </div>
                                                <span class="font-mono text-[11px] text-gray-500">{{ $off->subject?->units ?? 3 }} units</span>
                                            </div>
                                            <div class="font-serif font-bold text-navy mt-0.5">
                                                {{ $off->subject?->title }}
                                            </div>
                                            <div class="text-[11px] text-gray-500 mt-1 flex flex-wrap items-center gap-3">
                                                <span>Faculty: {{ $off->instructor?->name ?: ($off->instructor_name ?: ($off->subject?->instructor ?: 'TBA')) }}</span>
                                                @foreach ($off->schedules ?: [] as $s)
                                                    <span class="rounded bg-white px-1.5 py-0.5 border border-line text-[10px] font-mono">
                                                        {{ $s['day'] ?? '' }} {{ $s['time_start'] ?? '' }}-{{ $s['time_end'] ?? '' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-400 italic text-center py-6">
                                        No offerings available for the active academic term.
                                    </p>
                                @endforelse
                            </div>

                            <div class="pt-3 border-t border-line flex items-center justify-between">
                                <label class="inline-flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                    <input type="checkbox" name="force" value="1" class="rounded border-line text-navy focus:ring-gold" />
                                    <span>Override Conflict Warnings</span>
                                </label>

                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#0A192F] hover:bg-[#14365D] text-gold text-xs font-bold border border-gold/40 transition shadow-2xs">
                                    <x-icon name="check" class="h-4 w-4" />
                                    <span>Save Student Offerings</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="p-8 text-center text-gray-400 bg-gray-50 rounded-2xl border border-dashed border-line text-xs">
                            Select a student above to view, modify, and check time conflicts for their enrolled subject offerings.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
