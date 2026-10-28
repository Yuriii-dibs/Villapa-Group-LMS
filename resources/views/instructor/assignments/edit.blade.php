<x-layouts.app>
    <x-slot:title>Edit Assignment – Faculty Portal</x-slot:title>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-serif font-bold text-navy">Edit Assignment</h1>
                <p class="text-xs text-gray-500 mt-1">Update details, deadline, or points for {{ $assignment->title }}.</p>
            </div>
            <a href="{{ route('instructor.assignments.index') }}" class="text-xs font-semibold text-gray-500 hover:text-navy">
                &larr; Cancel and Return
            </a>
        </div>

        <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 sm:p-8 shadow-2xs">
            <form method="POST" action="{{ route('instructor.assignments.update', $assignment) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Subject *</label>
                    <select name="subject_id" required class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}" {{ old('subject_id', $assignment->subject_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->subject_code }} – {{ $s->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Assignment Title *</label>
                    <input type="text" name="title" value="{{ old('title', $assignment->title) }}" required
                           class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Due Date & Time</label>
                        <input type="datetime-local" name="due_date"
                               value="{{ old('due_date', $assignment->due_date ? $assignment->due_date->format('Y-m-d\TH:i') : '') }}"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Total Points *</label>
                        <input type="number" name="total_points" value="{{ old('total_points', $assignment->total_points) }}" required min="1" max="1000"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Short Description</label>
                    <input type="text" name="description" value="{{ old('description', $assignment->description) }}"
                           class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Detailed Instructions</label>
                    <textarea name="instructions" rows="4"
                              class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold">{{ old('instructions', $assignment->instructions) }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-line/60">
                    <form method="POST" action="{{ route('instructor.assignments.destroy', $assignment) }}" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                            Delete Assignment
                        </button>
                    </form>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('instructor.assignments.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-navy">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white text-xs font-bold border border-gold/40 hover:border-gold/70 shadow-sm transition">
                            <x-icon name="check" class="h-4 w-4 text-gold" />
                            <span>Save Changes</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
