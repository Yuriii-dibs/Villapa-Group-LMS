<x-layouts.app>
    <x-slot:title>Create Course Offering – Admin Console</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-serif font-bold text-navy">Create Course Offering</h1>
                <p class="text-xs text-gray-500 mt-1">Assign an academic subject to a term, section, instructor, and schedule slots.</p>
            </div>
            <a href="{{ route('admin.offerings.index') }}" class="text-xs font-semibold text-gray-500 hover:text-navy">
                &larr; Cancel and Return
            </a>
        </div>

        <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 sm:p-8 shadow-2xs"
             x-data="{
                 subjects: {{ json_encode($subjectsData) }},
                 selectedSubjectId: '{{ old('subject_id', '') }}',
                 selectedInstructorId: '{{ old('instructor_id', '') }}',
                 room: '{{ old('room', '') }}',
                 meetLink: '{{ old('meet_link', '') }}',
                 slots: {{ json_encode(old('slots', [])) }}.length ? {{ json_encode(old('slots', [])) }} : [
                     { day: 'Monday', time_start: '08:00 AM', time_end: '10:00 AM', room: 'Campus', type: 'Lec' }
                 ],
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
                         }
                     }
                 },
                 init() {
                     if (this.selectedSubjectId) {
                         this.onSubjectChange(this.selectedSubjectId);
                     }
                 },
                 addSlot() {
                     this.slots.push({ day: 'Wednesday', time_start: '08:00 AM', time_end: '10:00 AM', room: 'Campus', type: 'Lab' });
                 },
                 removeSlot(index) {
                     this.slots.splice(index, 1);
                 }
             }"
             x-init="init()">
            <form method="POST" action="{{ route('admin.offerings.store') }}" class="space-y-6">
                @csrf

                {{-- Primary Association Fields --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Academic Term *</label>
                        <select name="academic_term_id" required class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            @foreach ($terms as $t)
                                <option value="{{ $t->id }}" {{ $t->is_current ? 'selected' : '' }}>
                                    {{ $t->name }} {{ $t->is_current ? '(Active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subject *</label>
                        <select name="subject_id" required x-model="selectedSubjectId" @change="onSubjectChange($event.target.value)"
                                class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold font-semibold">
                            <option value="">Select Catalog Subject</option>
                            @foreach ($subjects as $s)
                                <option value="{{ $s->id }}">
                                    [{{ $s->subject_code }}] {{ $s->title }} ({{ $s->units }} Units) &bull; {{ $s->instructor ?: 'Unassigned' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Section Cohort</label>
                        <select name="section_id" class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            <option value="">-- No Section (General) --</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}" {{ (old('section_id', request('section_id')) == $sec->id) ? 'selected' : '' }}>
                                    {{ $sec->name }} (Year {{ $sec->year_level }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-gray-700">Assigned Faculty / Instructor</label>
                            <span class="text-[10px] text-gold-dark font-medium truncate max-w-[150px]" x-show="selectedInstructorName" x-text="'Catalog: ' + selectedInstructorName"></span>
                        </div>
                        <select name="instructor_id" x-model="selectedInstructorId"
                                class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold font-medium">
                            <option value="">-- Select Instructor --</option>
                            @foreach ($instructors as $inst)
                                <option value="{{ $inst->id }}">
                                    {{ $inst->name }} ({{ $inst->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Default Room</label>
                        <input type="text" name="room" x-model="room" placeholder="e.g. IT Lab 1 or ONLINE"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Google Meet Link</label>
                        <input type="url" name="meet_link" x-model="meetLink" placeholder="https://meet.google.com/xxx-yyyy-zzz"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                </div>

                {{-- Interactive Weekly Schedule Slots --}}
                <div class="pt-4 border-t border-line space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-serif text-sm font-bold text-navy">Weekly Schedule Slots</h3>
                            <p class="text-[11px] text-gray-500">Add day, start/end time, and room for this offering.</p>
                        </div>
                        <button type="button" @click="addSlot()" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 hover:text-navy">
                            <x-icon name="plus" class="h-3 w-3" />
                            <span>+ Add Slot</span>
                        </button>
                    </div>

                    <template x-for="(slot, i) in slots" :key="i">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 p-3 rounded-xl bg-gray-50 border border-line/60 items-center">
                            <div class="sm:col-span-3">
                                <select :name="'slots[' + i + '][day]'" x-model="slot.day" class="w-full text-xs rounded-lg border border-line px-2.5 py-1.5 bg-white">
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <input type="text" :name="'slots[' + i + '][time_start]'" x-model="slot.time_start" placeholder="07:30 AM" class="w-full text-xs rounded-lg border border-line px-2.5 py-1.5 bg-white font-mono" />
                            </div>
                            <div class="sm:col-span-3">
                                <input type="text" :name="'slots[' + i + '][time_end]'" x-model="slot.time_end" placeholder="10:30 AM" class="w-full text-xs rounded-lg border border-line px-2.5 py-1.5 bg-white font-mono" />
                            </div>
                            <div class="sm:col-span-2">
                                <select :name="'slots[' + i + '][type]'" x-model="slot.type" class="w-full text-xs rounded-lg border border-line px-2.5 py-1.5 bg-white">
                                    <option value="Lec">Lecture</option>
                                    <option value="Lab">Laboratory</option>
                                </select>
                            </div>
                            <div class="sm:col-span-1 text-center">
                                <button type="button" @click="removeSlot(i)" class="text-xs font-bold text-rose-600 hover:text-rose-800">
                                    &times;
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-line">
                    <a href="{{ route('admin.offerings.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-navy">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white text-xs font-bold border border-gold/40 hover:border-gold/70 shadow-sm transition">
                        <x-icon name="check" class="h-4 w-4 text-gold" />
                        <span>Create Offering</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
