<x-layouts.app>
    <x-slot:title>Course Offerings – Admin Console</x-slot:title>

    <div class="space-y-8">
        <x-page-header
            title="Course Offerings"
            subtitle="Manage semester subject offerings, assigned faculty, sections, and classroom schedules."
        >
            <x-slot:actions>
                <a href="{{ route('admin.offerings.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white border border-gold/40 rounded-xl text-xs sm:text-sm font-semibold hover:border-gold/70 shadow-sm transition">
                    <x-icon name="plus" class="h-4 w-4 text-gold" />
                    <span>+ New Offering</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Filters toolbar --}}
        <div class="rounded-2xl border border-line bg-[#FFFEFA] p-5 shadow-2xs">
            <form method="GET" action="{{ route('admin.offerings.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Filter by Academic Term</label>
                    <select name="term_id" class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                        <option value="">All Academic Terms</option>
                        @foreach ($terms as $t)
                            <option value="{{ $t->id }}" {{ $termId == $t->id ? 'selected' : '' }}>
                                {{ $t->name }} {{ $t->is_current ? '(Active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Filter by Section</label>
                    <select name="section_id" class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                        <option value="">All Sections</option>
                        @foreach ($sections as $s)
                            <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} (Year {{ $s->year_level }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 sm:col-span-2 justify-end">
                    <a href="{{ route('admin.offerings.index') }}" class="px-3.5 py-2 rounded-xl border border-line text-xs font-semibold text-gray-600 hover:text-navy">
                        Reset
                    </a>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#0A192F] text-gold text-xs font-bold border border-gold/40 hover:bg-[#14365D] transition">
                        Filter Offerings
                    </button>
                </div>
            </form>
        </div>

        {{-- Offerings Table --}}
        <div class="overflow-hidden rounded-2xl border border-line bg-[#FFFEFA] shadow-2xs">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/80 border-b border-line text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-3.5">Subject & Term</th>
                        <th class="px-5 py-3.5">Section</th>
                        <th class="px-5 py-3.5">Instructor</th>
                        <th class="px-5 py-3.5">Weekly Schedules</th>
                        <th class="px-5 py-3.5">Google Meet</th>
                        <th class="px-5 py-3.5">Enrolled</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($offerings as $offering)
                        <tr class="hover:bg-indigo-50/20 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="rounded bg-[#0A192F] px-2 py-0.5 font-mono text-[11px] font-bold text-gold">
                                        {{ $offering->subject?->subject_code ?? 'CODE' }}
                                    </span>
                                </div>
                                <div class="font-serif font-bold text-navy text-sm line-clamp-1">
                                    {{ $offering->subject?->title }}
                                </div>
                                <div class="text-[11px] text-gray-400">
                                    {{ $offering->academicTerm?->name ?? 'Term' }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($offering->section)
                                    <a href="{{ route('admin.sections.schedule', $offering->section) }}" title="View Section Timetable"
                                       class="inline-flex items-center gap-1 rounded bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 hover:border-indigo-300 transition">
                                        <x-icon name="calendar" class="h-3 w-3 text-indigo-500" />
                                        <span>{{ $offering->section->name }}</span>
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 italic">None</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-700 font-medium">
                                {{ $offering->instructor?->name ?: ($offering->instructor_name ?: 'TBA') }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600">
                                @forelse ($offering->schedules ?? [] as $slot)
                                    <div class="text-[11px]">
                                        <span class="font-bold text-navy">{{ $slot['day'] ?? '' }}</span>
                                        <span>{{ $slot['time_start'] ?? '' }}-{{ $slot['time_end'] ?? '' }}</span>
                                        <span class="text-gray-400 font-mono">({{ $slot['room'] ?? 'TBA' }})</span>
                                    </div>
                                @empty
                                    <span class="text-gray-400 italic text-[11px]">No slots</span>
                                @endforelse
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                                @if ($offering->meet_link)
                                    <a href="{{ $offering->meet_link }}" target="_blank" class="font-bold text-emerald-600 hover:underline">
                                        Active Link &rarr;
                                    </a>
                                @else
                                    <span class="text-gray-400 italic">None</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap font-bold text-navy text-xs">
                                {{ $offering->students_count }} students
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('admin.offerings.edit', $offering) }}" class="text-xs font-semibold text-indigo-700 hover:underline">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.offerings.destroy', $offering) }}" class="inline" onsubmit="return confirm('Delete this offering?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400 italic">No offerings found matching filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $offerings->links() }}
        </div>
    </div>
</x-layouts.app>
