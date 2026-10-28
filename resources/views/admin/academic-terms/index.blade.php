<x-layouts.app>
    <x-slot:title>Academic Terms – Admin Console</x-slot:title>

    <div class="space-y-8">
        <x-page-header
            title="Academic Terms"
            subtitle="Configure semesters, academic calendars, start/end dates, and designate the active term."
        >
            <x-slot:actions>
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 border border-line rounded-xl text-xs sm:text-sm font-semibold text-navy bg-white hover:border-gold/50 transition">
                    &larr; Dashboard
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Add Academic Term Form --}}
        <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 shadow-2xs">
            <h2 class="font-serif text-base font-bold text-navy mb-4">Add New Academic Term</h2>
            <form method="POST" action="{{ route('admin.terms.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Term Name *</label>
                        <input type="text" name="name" required placeholder="e.g. 1st Semester AY 2026–2027"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Academic Year *</label>
                        <input type="text" name="academic_year" required value="2026-2027" placeholder="e.g. 2026-2027"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Semester *</label>
                        <select name="semester" class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                            <option value="Summer">Summer Term</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Start Date *</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">End Date *</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d', strtotime('+5 months')) }}"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-navy">
                            <input type="checkbox" name="is_current" value="1" class="rounded border-line text-navy focus:ring-gold" />
                            <span>Mark as Active Term</span>
                        </label>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0A192F] hover:bg-[#14365D] text-gold text-xs font-bold border border-gold/40 transition">
                            <x-icon name="plus" class="h-4 w-4" />
                            <span>Create Term</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Academic Terms Table --}}
        <div class="overflow-hidden rounded-2xl border border-line bg-[#FFFEFA] shadow-2xs">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/80 border-b border-line text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-3.5">Term Name</th>
                        <th class="px-5 py-3.5">Academic Year</th>
                        <th class="px-5 py-3.5">Semester</th>
                        <th class="px-5 py-3.5">Duration (Start – End)</th>
                        <th class="px-5 py-3.5">Offerings</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($terms as $term)
                        <tr class="hover:bg-indigo-50/20 transition">
                            <td class="px-5 py-3.5 font-bold font-serif text-navy">
                                {{ $term->name }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600 font-mono">
                                {{ $term->academic_year }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600">
                                {{ $term->semester }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">
                                {{ $term->start_date->format('M d, Y') }} &ndash; {{ $term->end_date->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-navy font-bold">
                                {{ $term->offerings_count }} offerings
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($term->is_current)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Active Term
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                @if (!$term->is_current)
                                    <form method="POST" action="{{ route('admin.terms.set-current', $term) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-xs font-bold text-gold-dark hover:underline">
                                            Make Active
                                        </button>
                                    </form>
                                @endif

                                <div x-data="{ open: false }" class="inline-block">
                                    <button @click="open = true" class="px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:underline">
                                        Edit
                                    </button>
                                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
                                        <div class="fixed inset-0 bg-gray-900/60" @click="open = false"></div>
                                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10 text-left">
                                            <h3 class="font-bold text-gray-900 mb-4">Edit {{ $term->name }}</h3>
                                            <form method="POST" action="{{ route('admin.terms.update', $term) }}" class="space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Name</label>
                                                    <input type="text" name="name" value="{{ $term->name }}" required class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Academic Year</label>
                                                        <input type="text" name="academic_year" value="{{ $term->academic_year }}" required class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Semester</label>
                                                        <input type="text" name="semester" value="{{ $term->semester }}" required class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Start Date</label>
                                                        <input type="date" name="start_date" value="{{ $term->start_date->format('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">End Date</label>
                                                        <input type="date" name="end_date" value="{{ $term->end_date->format('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-line px-3 py-2" />
                                                    </div>
                                                </div>
                                                <div class="flex flex-row-reverse gap-2 pt-2 border-t border-line">
                                                    <button type="submit" class="px-4 py-2 bg-[#0A192F] text-gold font-bold text-xs rounded-xl">Save</button>
                                                    <button type="button" @click="open = false" class="px-4 py-2 border rounded-xl text-xs">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.terms.destroy', $term) }}" class="inline" onsubmit="return confirm('Delete academic term {{ $term->name }}?');">
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
                            <td colspan="7" class="p-8 text-center text-gray-400 italic">No academic terms created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
