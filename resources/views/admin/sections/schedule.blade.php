<x-layouts.app>
    <x-slot:title>Section {{ $section->name }} Schedule – School Administrator</x-slot:title>

    <div class="space-y-6" x-data="{ addSubjectOpen: false }">

        {{-- ── 1. HEADER & ACTION TOOLBAR ────────────────────────────────────── --}}
        <x-page-header
            title="Section {{ $section->name }} Timetable"
            subtitle="Manage scheduled subjects, timetable slots, instructors, and classrooms for this cohort."
        >
            <x-slot:actions>
                <a href="{{ route('admin.sections.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-white px-3.5 py-2 text-xs sm:text-sm font-semibold text-navy hover:bg-[#FAF8F5] transition shadow-2xs">
                    <span>&larr;</span> All Sections
                </a>
                <a href="{{ route('admin.enrollment.index', ['section_id' => $section->id]) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-white px-3.5 py-2 text-xs sm:text-sm font-semibold text-navy hover:bg-[#FAF8F5] transition shadow-2xs">
                    <x-icon name="users" class="h-4 w-4 text-navy" />
                    <span>Enroll Students</span>
                </a>
                <button type="button" @click="addSubjectOpen = true"
                        class="inline-flex items-center gap-2 rounded-xl border border-gold/40 bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] px-4 py-2 text-xs sm:text-sm font-bold text-gold hover:border-gold/70 hover:text-white transition shadow-sm">
                    <x-icon name="plus" class="h-4 w-4 text-gold" />
                    <span>Schedule Subject</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        {{-- ── 2. SECTION SUMMARY BANNER & TERM PICKER ───────────────────────── --}}
        <div class="rounded-2xl bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] p-5 sm:p-6 text-paper shadow-md border border-gold/30">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center rounded-lg bg-gold/20 px-3 py-1 font-mono text-xs font-bold text-gold border border-gold/40">
                            Section {{ $section->name }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-gray-200">
                            {{ $section->program }} &bull; Year {{ $section->year_level }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-gray-200">
                            {{ $section->students()->count() }} Enrolled Students
                        </span>
                    </div>
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-white tracking-tight">
                        Academic Class Schedule
                    </h2>
                </div>

                {{-- Term Selector & Stats Pills --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <form method="GET" action="{{ route('admin.sections.schedule', $section) }}" class="flex items-center gap-2">
                        <label for="term_id" class="sr-only">Academic Term</label>
                        <select id="term_id" name="term_id" onchange="this.form.submit()"
                                class="rounded-xl border border-white/20 bg-[#071321] text-xs font-semibold text-gold px-3 py-2 focus:ring-gold focus:border-gold">
                            @foreach ($terms as $t)
                                <option value="{{ $t->id }}" {{ $termId == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} {{ $t->is_current ? '(Active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <div class="rounded-xl bg-white/10 backdrop-blur-xs px-4 py-2 border border-white/15 text-center">
                        <span class="block font-mono text-lg font-bold text-gold">{{ $offerings->count() }}</span>
                        <span class="text-[10px] uppercase tracking-wider text-gray-300 font-medium">Subjects</span>
                    </div>

                    <div class="rounded-xl bg-white/10 backdrop-blur-xs px-4 py-2 border border-white/15 text-center">
                        <span class="block font-mono text-lg font-bold text-white">{{ $totalSessions }}</span>
                        <span class="text-[10px] uppercase tracking-wider text-gray-300 font-medium">Weekly Slots</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 3. SESSION STATUS & WARNING NOTICES ─────────────────────────────── --}}
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs sm:text-sm font-semibold text-emerald-800 shadow-2xs">
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 shadow-2xs">
                <div class="flex items-start gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-200 text-amber-900 font-bold text-sm">!</span>
                    <div>
                        <h4 class="font-serif text-sm font-bold text-amber-950">Timetable Conflict Notice</h4>
                        <p class="text-xs text-amber-900 mt-1 leading-relaxed">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 4. TIMETABLE VIEW (ORGANIZED PER DAY) ──────────────────────────── --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-line/60">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gold/15 text-gold border border-gold/40">
                        <x-icon name="clock" class="h-4 w-4" />
                    </span>
                    <h3 class="font-serif text-base font-bold text-navy">Weekly Timetable Matrix</h3>
                </div>
                <span class="text-xs font-semibold text-gray-500">Organized Monday through Saturday</span>
            </div>

            @if ($totalSessions === 0)
                <div class="rounded-2xl border border-line/80 bg-[#FFFEFA] p-10 text-center shadow-2xs">
                    <x-icon name="calendar" class="mx-auto h-12 w-12 text-gray-300 mb-2" />
                    <h4 class="font-serif text-base font-bold text-navy">No subjects scheduled for Section {{ $section->name }} yet</h4>
                    <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">
                        Click the "Schedule Subject" button above to assign an academic subject, professor, day, and time slots to this section.
                    </p>
                    <div class="mt-4">
                        <button type="button" @click="addSubjectOpen = true"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-[#0A192F] px-4 py-2 text-xs font-bold text-gold border border-gold/50 shadow-sm hover:bg-[#14365D] hover:text-white transition">
                            <x-icon name="plus" class="h-4 w-4" />
                            <span>Schedule First Subject</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                        @php $slots = $scheduleByDay[$day] ?? []; @endphp
                        <div class="rounded-2xl border border-line/80 bg-[#FFFEFA] overflow-hidden shadow-2xs flex flex-col justify-between">
                            {{-- Day Header --}}
                            <div class="bg-[#FAF8F5] px-4 py-3 border-b border-line/60 flex items-center justify-between">
                                <span class="font-serif font-bold text-navy text-sm">{{ $day }}</span>
                                <span class="inline-flex items-center rounded-full bg-white px-2 py-0.5 text-[10px] font-mono font-bold text-gray-600 border border-line">
                                    {{ count($slots) }} {{ Str::plural('Class', count($slots)) }}
                                </span>
                            </div>

                            {{-- Day Slots List --}}
                            <div class="p-3.5 space-y-3 flex-1">
                                @forelse ($slots as $item)
                                    <div class="rounded-xl border border-line bg-white p-3 hover:border-gold/60 transition shadow-2xs">
                                        <div class="flex items-start justify-between gap-2 mb-1.5">
                                            <span class="inline-flex items-center rounded bg-[#0A192F] px-2 py-0.5 font-mono text-[11px] font-bold text-gold">
                                                {{ $item['subject_code'] }}
                                            </span>
                                            <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-1.5 py-0.2 rounded">
                                                {{ $item['type'] }} &bull; {{ $item['units'] }} Units
                                            </span>
                                        </div>

                                        <h5 class="font-serif text-xs font-bold text-navy leading-snug line-clamp-2">
                                            {{ $item['title'] }}
                                        </h5>

                                        <div class="mt-2 text-[11px] text-gray-600 space-y-0.5">
                                            <div class="flex items-center gap-1 font-mono font-semibold text-navy">
                                                <x-icon name="clock" class="h-3 w-3 text-gold" />
                                                <span>{{ $item['time_start'] }} &ndash; {{ $item['time_end'] }}</span>
                                                <span class="text-gray-400 font-normal">({{ $item['duration'] }})</span>
                                            </div>
                                            <div class="truncate">
                                                Room: <strong>{{ $item['room'] }}</strong>
                                            </div>
                                            <div class="truncate">
                                                Faculty: <strong>{{ $item['instructor'] }}</strong>
                                            </div>
                                        </div>

                                        <div class="mt-2.5 pt-2 border-t border-line/50 flex items-center justify-between text-[11px]">
                                            @if ($item['meet_link'])
                                                <a href="{{ $item['meet_link'] }}" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1 text-emerald-700 hover:underline font-semibold">
                                                    <x-icon name="video" class="h-3 w-3" />
                                                    <span>Google Meet</span>
                                                </a>
                                            @else
                                                <span class="text-gray-400">On Campus</span>
                                            @endif

                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.offerings.edit', $item['offering']) }}"
                                                   class="text-navy hover:text-gold font-bold transition">
                                                    Edit
                                                </a>
                                                <span>&bull;</span>
                                                <form method="POST" action="{{ route('admin.offerings.destroy', $item['offering']) }}"
                                                      onsubmit="return confirm('Remove {{ $item['subject_code'] }} from Section {{ $section->name }}?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold transition">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-xs text-gray-400 italic">
                                        No classes scheduled on {{ $day }}.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── 5. ALL SCHEDULED OFFERINGS FOR THIS SECTION ─────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border border-line/80 bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)]">
            <div class="px-5 py-3.5 bg-[#FAF8F5] border-b border-line flex items-center justify-between">
                <h3 class="font-serif text-sm font-bold text-navy">Subjects Catalog Roster &mdash; Section {{ $section->name }}</h3>
                <span class="text-xs text-gray-500">{{ $offerings->count() }} Subjects Scheduled</span>
            </div>

            @if ($offerings->isEmpty())
                <div class="p-8 text-center text-xs text-gray-400">
                    No subject offerings currently attached to Section {{ $section->name }}.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm divide-y divide-line/60">
                        <thead class="bg-gray-50/70 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Subject</th>
                                <th class="px-5 py-3">Assigned Faculty</th>
                                <th class="px-5 py-3">Weekly Timetable Slots</th>
                                <th class="px-5 py-3">Classroom / Room</th>
                                <th class="px-5 py-3">Enrolled Students</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line/50 bg-white">
                            @foreach ($offerings as $offering)
                                <tr class="hover:bg-[#FAF8F5]/60 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <span class="rounded bg-[#0A192F] px-2 py-0.5 font-mono text-[11px] font-bold text-gold">
                                                {{ $offering->subject?->subject_code ?? 'SUBJ' }}
                                            </span>
                                            <span class="text-xs text-gray-400 font-semibold">{{ $offering->subject?->units ?? 3 }} Units</span>
                                        </div>
                                        <div class="font-serif font-bold text-navy text-sm">
                                            {{ $offering->subject?->title }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-gray-700 font-medium">
                                        {{ $offering->instructor?->name ?: ($offering->instructor_name ?: ($offering->subject?->instructor ?: 'TBA')) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-gray-600">
                                        @forelse ($offering->schedules ?? [] as $slot)
                                            <div class="text-[11px] font-mono">
                                                <strong class="text-navy">{{ $slot['day'] ?? '' }}</strong>:
                                                {{ $slot['time_start'] ?? '' }} &ndash; {{ $slot['time_end'] ?? '' }}
                                                <span class="text-gray-400">({{ $slot['type'] ?? 'Lec' }})</span>
                                            </div>
                                        @empty
                                            <span class="text-gray-400 italic text-[11px]">No slots</span>
                                        @endforelse
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-gray-700">
                                        {{ $offering->room ?: 'Campus Room' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-bold text-navy">
                                        {{ $offering->students_count }} students
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                        <a href="{{ route('admin.offerings.edit', $offering) }}"
                                           class="inline-flex items-center gap-1 rounded-lg border border-line bg-white hover:border-gold px-2.5 py-1 text-xs font-semibold text-navy transition shadow-2xs">
                                            <x-icon name="edit" class="h-3.5 w-3.5 text-navy" />
                                            <span>Edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.offerings.destroy', $offering) }}"
                                              onsubmit="return confirm('Delete course offering for {{ $offering->subject?->subject_code }} in Section {{ $section->name }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700 transition shadow-2xs">
                                                <x-icon name="trash" class="h-3.5 w-3.5 text-rose-600" />
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ── 6. SCHEDULE SUBJECT MODAL (Teleported to body) ─────────────────── --}}
        <template x-teleport="body">
            <div x-show="addSubjectOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div x-show="addSubjectOpen" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity" @click="addSubjectOpen = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div x-show="addSubjectOpen"
                         x-data="{
                             subjects: {{ json_encode($subjectsData) }},
                             selectedSubjectId: '{{ old('subject_id', '') }}',
                             selectedInstructorId: '{{ old('instructor_id', '') }}',
                             room: '{{ old('room', '') }}',
                             meetLink: '{{ old('meet_link', '') }}',
                             slots: {{ json_encode(old('slots', [])) }}.length ? {{ json_encode(old('slots', [])) }} : [],
                             get selectedInstructorName() {
                                 const found = this.subjects.find(s => s.id == this.selectedSubjectId);
                                 return found ? (found.instructor_name || '') : '';
                             },
                             onSubjectChange(subjectId) {
                                 this.selectedSubjectId = subjectId;
                                 const found = this.subjects.find(s => s.id == subjectId);
                                 if (found) {
                                     if (found.instructor_id) {
                                         this.selectedInstructorId = String(found.instructor_id);
                                     }
                                     if (found.meet_link && !this.meetLink) {
                                         this.meetLink = found.meet_link;
                                     }
                                     if (found.schedules && found.schedules.length) {
                                         this.slots = JSON.parse(JSON.stringify(found.schedules));
                                     } else if (!this.slots.length) {
                                         this.slots = [{ day: 'Monday', time_start: '08:00 AM', time_end: '10:00 AM', room: 'Campus Room', type: 'Lec' }];
                                     }
                                 }
                             },
                             init() {
                                 if (this.selectedSubjectId) {
                                     this.onSubjectChange(this.selectedSubjectId);
                                 } else if (!this.slots.length) {
                                     this.slots = [{ day: 'Monday', time_start: '08:00 AM', time_end: '10:00 AM', room: 'Campus Room', type: 'Lec' }];
                                 }
                             },
                             addSlot() {
                                 this.slots.push({ day: 'Wednesday', time_start: '08:00 AM', time_end: '10:00 AM', room: 'Campus Room', type: 'Lab' });
                             },
                             removeSlot(idx) {
                                 this.slots.splice(idx, 1);
                             }
                         }"
                         x-init="init()"
                         class="relative inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle bg-[#FFFEFA] rounded-2xl shadow-2xl border border-line transform transition-all">
                        
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-line/60">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold/15 text-gold border border-gold/40">
                                    <x-icon name="calendar" class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="font-serif text-lg font-bold text-navy">Schedule Subject for Section {{ $section->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $section->program }} &bull; Year {{ $section->year_level }} Cohort</p>
                                </div>
                            </div>
                            <button type="button" @click="addSubjectOpen = false" class="text-gray-400 hover:text-navy">
                                <x-icon name="close" class="h-5 w-5" />
                            </button>
                        </div>

                        <form method="POST" action="{{ route('admin.sections.schedule.store', $section) }}" class="space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Academic Term *</label>
                                    <select name="academic_term_id" required class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                        @foreach ($terms as $t)
                                            <option value="{{ $t->id }}" {{ $termId == $t->id ? 'selected' : '' }}>
                                                {{ $t->name }} {{ $t->is_current ? '(Active)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Catalog Subject *</label>
                                    <select name="subject_id" required x-model="selectedSubjectId" @change="onSubjectChange($event.target.value)"
                                            class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold font-semibold">
                                        <option value="">-- Select Subject --</option>
                                        @foreach ($subjects as $s)
                                            <option value="{{ $s->id }}">
                                                [{{ $s->subject_code }}] {{ $s->title }} ({{ $s->units }} Units) &bull; {{ $s->instructor ?: 'Unassigned' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-navy">Assigned Faculty / Instructor</label>
                                        <span class="text-[10px] text-gold-dark font-medium truncate max-w-[150px]" x-show="selectedInstructorName" x-text="'Catalog: ' + selectedInstructorName"></span>
                                    </div>
                                    <select name="instructor_id" x-model="selectedInstructorId"
                                            class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold font-medium">
                                        <option value="">-- Select Faculty --</option>
                                        @foreach ($instructors as $inst)
                                            <option value="{{ $inst->id }}">
                                                {{ $inst->name }} ({{ $inst->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Default Classroom / Room</label>
                                    <input type="text" name="room" x-model="room" placeholder="e.g. IT Lab 1 or Room 302"
                                           class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Google Meet Link</label>
                                <input type="url" name="meet_link" x-model="meetLink" placeholder="https://meet.google.com/xxx-yyyy-zzz"
                                       class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            </div>
                                       class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            </div>

                            {{-- Weekly Schedule Slots --}}
                            <div class="pt-3 border-t border-line/60">
                                <div class="flex items-center justify-between mb-2.5">
                                    <span class="text-xs font-bold uppercase tracking-wider text-navy">Weekly Schedule Slots</span>
                                    <button type="button" @click="addSlot()" class="inline-flex items-center gap-1 text-xs font-bold text-gold-dark hover:text-navy">
                                        <x-icon name="plus" class="h-3.5 w-3.5" />
                                        <span>Add Slot</span>
                                    </button>
                                </div>

                                <div class="space-y-2.5">
                                    <template x-for="(slot, idx) in slots" :key="idx">
                                        <div class="flex items-center gap-2 p-2.5 rounded-xl border border-line bg-[#FAF8F5]">
                                            <div class="w-28 shrink-0">
                                                <select :name="'slots[' + idx + '][day]'" x-model="slot.day" required
                                                        class="w-full rounded-lg border border-line bg-white text-xs px-2 py-1.5 text-navy">
                                                    <option value="Monday">Monday</option>
                                                    <option value="Tuesday">Tuesday</option>
                                                    <option value="Wednesday">Wednesday</option>
                                                    <option value="Thursday">Thursday</option>
                                                    <option value="Friday">Friday</option>
                                                    <option value="Saturday">Saturday</option>
                                                </select>
                                            </div>

                                            <div class="w-24 shrink-0">
                                                <input type="text" :name="'slots[' + idx + '][time_start]'" x-model="slot.time_start" placeholder="08:00 AM" required
                                                       class="w-full rounded-lg border border-line bg-white text-xs px-2 py-1.5 font-mono text-navy">
                                            </div>

                                            <div class="w-24 shrink-0">
                                                <input type="text" :name="'slots[' + idx + '][time_end]'" x-model="slot.time_end" placeholder="10:00 AM" required
                                                       class="w-full rounded-lg border border-line bg-white text-xs px-2 py-1.5 font-mono text-navy">
                                            </div>

                                            <div class="w-20 shrink-0">
                                                <select :name="'slots[' + idx + '][type]'" x-model="slot.type"
                                                        class="w-full rounded-lg border border-line bg-white text-xs px-2 py-1.5 text-navy">
                                                    <option value="Lec">Lec</option>
                                                    <option value="Lab">Lab</option>
                                                </select>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <input type="text" :name="'slots[' + idx + '][room]'" x-model="slot.room" placeholder="Room"
                                                       class="w-full rounded-lg border border-line bg-white text-xs px-2 py-1.5 text-navy">
                                            </div>

                                            <button type="button" @click="removeSlot(idx)" :disabled="slots.length <= 1"
                                                    class="shrink-0 p-1.5 text-rose-500 hover:text-rose-700 disabled:opacity-30 disabled:cursor-not-allowed">
                                                <x-icon name="close" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="pt-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="force" value="1" class="rounded border-gray-300 text-gold focus:ring-gold">
                                    <span class="text-xs text-gray-600">Override Conflict (Force scheduling if time slot overlaps with another subject)</span>
                                </label>
                            </div>

                            <div class="mt-5 pt-3 border-t border-line/60 flex flex-row-reverse gap-2">
                                <button type="submit" class="rounded-xl bg-[#0A192F] px-5 py-2.5 text-xs font-bold text-gold border border-gold/50 shadow-sm hover:bg-[#14365D] hover:text-white transition">
                                    Schedule Subject
                                </button>
                                <button type="button" @click="addSubjectOpen = false" class="rounded-xl border border-line bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-[#FAF8F5] transition">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-layouts.app>
