<x-layouts.app>
    <x-slot:title>Create Subject Assignment – Faculty Portal</x-slot:title>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-serif font-bold text-navy">Create Assignment</h1>
                <p class="text-xs text-gray-500 mt-1">Publish an assignment, activity, or laboratory task for your enrolled students.</p>
            </div>
            <a href="{{ route('instructor.assignments.index') }}" class="text-xs font-semibold text-gray-500 hover:text-navy">
                &larr; Cancel and Return
            </a>
        </div>

        <div class="rounded-2xl border border-line bg-[#FFFEFA] p-6 sm:p-8 shadow-2xs">
            <form method="POST" action="{{ route('instructor.assignments.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Subject *</label>
                    <select name="subject_id" required class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                        <option value="">Select Enrolled Subject</option>
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}" {{ old('subject_id', $selectedSubjectId) == $s->id ? 'selected' : '' }}>
                                {{ $s->subject_code }} – {{ $s->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Assignment Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Laboratory Activity 1: Subnetting & IP Configuration"
                           class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    @error('title') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Due Date & Time</label>
                        <input type="datetime-local" name="due_date" value="{{ old('due_date') }}"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                        @error('due_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Total Points *</label>
                        <input type="number" name="total_points" value="{{ old('total_points', 100) }}" required min="1" max="1000"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                        @error('total_points') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Short Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Brief overview of the activity"
                           class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    @error('description') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Detailed Instructions</label>
                    <textarea name="instructions" rows="4" placeholder="Specify instructions, rubric, accepted submission formats (file upload, written text, or cloud link)..."
                              class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold">{{ old('instructions') }}</textarea>
                    @error('instructions') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-line/60">
                    <a href="{{ route('instructor.assignments.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-navy">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white text-xs font-bold border border-gold/40 hover:border-gold/70 shadow-sm transition">
                        <x-icon name="check" class="h-4 w-4 text-gold" />
                        <span>Publish Assignment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
