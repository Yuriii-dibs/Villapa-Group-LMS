<x-layouts.app>
    <x-slot:title>Subjects Catalog – Admin Console</x-slot:title>

    <div class="space-y-8">
        <x-page-header
            title="Subjects Catalog"
            subtitle="Manage institutional subject curriculums, course codes, year levels, and units."
        >
            <x-slot:actions>
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 border border-line rounded-xl text-xs sm:text-sm font-semibold text-navy bg-white hover:border-gold/50 transition">
                    &larr; Dashboard
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Add Subject Form Card --}}
        <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 shadow-2xs">
            <h2 class="font-serif text-base font-bold text-navy mb-4">Add New Subject to Catalog</h2>
            <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subject Code *</label>
                        <input type="text" name="subject_code" required placeholder="e.g. IT 311"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subject Name / Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Advanced Database Systems"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Units *</label>
                        <input type="number" name="units" required min="1" max="10" value="3"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Degree Program *</label>
                        <input type="text" name="program" required value="BSIT" placeholder="e.g. BSIT"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Year Level *</label>
                        <select name="year_level" required class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3" selected>3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Assigned Instructor</label>
                        <select name="instructor" class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            <option value="">-- Select Faculty Instructor --</option>
                            @foreach ($instructors as $inst)
                                <option value="{{ $inst->name }}">{{ $inst->name }} ({{ $inst->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#0A192F] hover:bg-[#14365D] text-gold text-xs font-bold border border-gold/40 transition">
                            <x-icon name="plus" class="h-4 w-4" />
                            <span>Add Subject</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Subjects Catalog Table --}}
        <div class="overflow-hidden rounded-2xl border border-line bg-[#FFFEFA] shadow-2xs">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/80 border-b border-line text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-3.5">Code</th>
                        <th class="px-5 py-3.5">Subject Name</th>
                        <th class="px-5 py-3.5">Year Level</th>
                        <th class="px-5 py-3.5">Units</th>
                        <th class="px-5 py-3.5">Program</th>
                        <th class="px-5 py-3.5">Offerings</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($subjects as $subject)
                        <tr class="hover:bg-indigo-50/20 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-navy">
                                <span class="rounded bg-[#0A192F] px-2 py-0.5 text-xs text-gold">
                                    {{ $subject->subject_code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-bold font-serif text-navy">
                                {{ $subject->title }}
                                @if ($subject->instructor)
                                    <div class="text-[11px] font-sans font-normal text-gray-500">
                                        Faculty: {{ $subject->instructor }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-600">
                                Year {{ $subject->year_level }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap font-mono text-xs font-bold text-navy">
                                {{ $subject->units ?? 3 }} units
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-600 font-semibold">
                                {{ $subject->program }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-navy font-bold">
                                {{ $subject->offerings_count }} active
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                <div x-data="{ open: false }" class="inline-block">
                                    <button @click="open = true" class="px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:underline">
                                        Edit
                                    </button>
                                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
                                        <div class="fixed inset-0 bg-gray-900/60" @click="open = false"></div>
                                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10 text-left">
                                            <h3 class="font-bold text-gray-900 mb-4">Edit {{ $subject->subject_code }}</h3>
                                            <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Code</label>
                                                        <input type="text" name="subject_code" value="{{ $subject->subject_code }}" required class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Units</label>
                                                        <input type="number" name="units" value="{{ $subject->units ?? 3 }}" min="1" max="10" required class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Title</label>
                                                    <input type="text" name="title" value="{{ $subject->title }}" required class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Program</label>
                                                        <input type="text" name="program" value="{{ $subject->program }}" required class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Year Level</label>
                                                        <select name="year_level" class="w-full text-xs rounded-xl border border-line px-3 py-2">
                                                            <option value="1" {{ $subject->year_level == 1 ? 'selected' : '' }}>1st Year</option>
                                                            <option value="2" {{ $subject->year_level == 2 ? 'selected' : '' }}>2nd Year</option>
                                                            <option value="3" {{ $subject->year_level == 3 ? 'selected' : '' }}>3rd Year</option>
                                                            <option value="4" {{ $subject->year_level == 4 ? 'selected' : '' }}>4th Year</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Assigned Faculty Instructor</label>
                                                    <select name="instructor" class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold">
                                                        <option value="">-- Select Faculty Instructor --</option>
                                                        @foreach ($instructors as $inst)
                                                            <option value="{{ $inst->name }}" {{ $subject->instructor === $inst->name ? 'selected' : '' }}>
                                                                {{ $inst->name }} ({{ $inst->email }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="flex flex-row-reverse gap-2 pt-2 border-t border-line">
                                                    <button type="submit" class="px-4 py-2 bg-[#0A192F] text-gold font-bold text-xs rounded-xl">Save</button>
                                                    <button type="button" @click="open = false" class="px-4 py-2 border rounded-xl text-xs">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline" onsubmit="return confirm('Delete subject {{ $subject->subject_code }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-rose-600 hover:underline">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400 italic">No subjects in catalog yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
