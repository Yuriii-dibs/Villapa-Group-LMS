<x-layouts.app>
    <x-slot:title>Sections & Year Levels – Admin Console</x-slot:title>

    <div class="space-y-8">
        <x-page-header
            title="Year Levels & Sections"
            subtitle="Manage student class sections, cohorts, and year-level designations."
        >
            <x-slot:actions>
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 border border-line rounded-xl text-xs sm:text-sm font-semibold text-navy bg-white hover:border-gold/50 transition">
                    &larr; Dashboard
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Add Section Card --}}
        <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 shadow-2xs">
            <h2 class="font-serif text-base font-bold text-navy mb-4">Add New Section / Cohort</h2>
            <form method="POST" action="{{ route('admin.sections.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Section Code / Name *</label>
                    <input type="text" name="name" required placeholder="e.g. IT3-J or IT3-A"
                           class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                </div>
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
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#0A192F] hover:bg-[#14365D] text-gold text-xs font-bold border border-gold/40 transition">
                        <x-icon name="plus" class="h-4 w-4" />
                        <span>Create Section</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Sections List --}}
        <div class="overflow-hidden rounded-2xl border border-line bg-[#FFFEFA] shadow-2xs">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/80 border-b border-line text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-3.5">Section Name</th>
                        <th class="px-5 py-3.5">Program</th>
                        <th class="px-5 py-3.5">Year Level</th>
                        <th class="px-5 py-3.5">Assigned Students</th>
                        <th class="px-5 py-3.5">Active Offerings</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($sections as $section)
                        <tr class="hover:bg-indigo-50/20 transition">
                            <td class="px-5 py-3.5 font-bold font-mono text-navy">
                                {{ $section->name }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600 font-semibold">
                                {{ $section->program }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600">
                                Year {{ $section->year_level }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-navy font-bold">
                                {{ $section->students_count }} students
                            </td>
                            <td class="px-5 py-3.5 text-xs text-navy font-bold">
                                {{ $section->offerings_count }} offerings
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <a href="{{ route('admin.sections.schedule', $section) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold text-navy bg-[#FFFEFA] border border-gold/50 hover:bg-[#0A192F] hover:text-gold rounded-lg shadow-2xs transition">
                                    <x-icon name="calendar" class="h-3.5 w-3.5 text-gold" />
                                    <span>Schedule ({{ $section->offerings_count }})</span>
                                </a>

                                <a href="{{ route('admin.enrollment.index', ['section_id' => $section->id]) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white border border-line hover:border-gold/60 rounded-lg shadow-2xs transition">
                                    <x-icon name="users" class="h-3.5 w-3.5 text-navy" />
                                    <span>Students</span>
                                </a>

                                <div x-data="{ open: false }" class="inline-block">
                                    <button @click="open = true" class="px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:underline">
                                        Edit
                                    </button>
                                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
                                        <div class="fixed inset-0 bg-gray-900/60" @click="open = false"></div>
                                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10 text-left">
                                            <h3 class="font-bold text-gray-900 mb-4">Edit Section {{ $section->name }}</h3>
                                            <form method="POST" action="{{ route('admin.sections.update', $section) }}" class="space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Section Name</label>
                                                    <input type="text" name="name" value="{{ $section->name }}" required
                                                           class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Program</label>
                                                    <input type="text" name="program" value="{{ $section->program }}" required
                                                           class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Year Level</label>
                                                    <select name="year_level" class="w-full text-xs rounded-xl border border-line px-3 py-2">
                                                        <option value="1" {{ $section->year_level == 1 ? 'selected' : '' }}>1st Year</option>
                                                        <option value="2" {{ $section->year_level == 2 ? 'selected' : '' }}>2nd Year</option>
                                                        <option value="3" {{ $section->year_level == 3 ? 'selected' : '' }}>3rd Year</option>
                                                        <option value="4" {{ $section->year_level == 4 ? 'selected' : '' }}>4th Year</option>
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

                                <form method="POST" action="{{ route('admin.sections.destroy', $section) }}" class="inline" onsubmit="return confirm('Delete section {{ $section->name }}?');">
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
                            <td colspan="6" class="p-8 text-center text-gray-400 italic">No sections configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
