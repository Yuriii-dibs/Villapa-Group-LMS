<x-layouts.app>
    <x-slot:title>Edit {{ $course->title }} - Instructor Portal</x-slot:title>

    <div class="max-w-5xl mx-auto space-y-8">
        @if (session('status'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg text-sm shadow-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r-lg text-sm shadow-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Status & Actions Banner -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold uppercase text-gray-400">Current Status:</span>
                    @if ($course->publication_status === 'published')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Published</span>
                    @elseif ($course->approval_status === 'pending')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Pending Admin Review</span>
                    @elseif ($course->approval_status === 'rejected')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">Changes Requested (Rejected)</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">Draft</span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight mt-2">{{ $course->title }}</h1>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if (in_array($course->approval_status, ['draft', 'rejected'], true))
                    <form method="POST" action="{{ route('instructor.courses.submit', $course) }}">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 shadow-sm transition">
                            Submit for Approval &rarr;
                        </button>
                    </form>
                @endif

                <!-- Delete Subject with Confirmation Modal -->
                <x-confirm-delete-modal
                    :action="route('instructor.courses.destroy', $course)"
                    title="Delete Subject"
                    message="Are you sure you want to permanently delete '{{ $course->title }}'? All syllabus modules, lessons, and enrollment progress records will be removed."
                    button-text="Delete Subject"
                    button-class="px-4 py-2.5 bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 rounded-xl text-sm font-bold transition" />
            </div>
        </div>

        <!-- Rejection Reason Notice (If Rejected) -->
        @if ($course->approval_status === 'rejected')
            @php
                $rejectionReview = $course->reviews->firstWhere('decision', 'rejected');
            @endphp
            @if ($rejectionReview && $rejectionReview->reason)
                <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-black text-red-800 uppercase tracking-wide">Administrator Feedback:</h3>
                    <p class="text-sm text-red-700 mt-2 leading-relaxed">{{ $rejectionReview->reason }}</p>
                    <p class="text-xs text-red-500 mt-3 font-medium">Please address the requested updates below and re-submit your subject for review.</p>
                </div>
            @endif
        @endif

        <!-- Lesson Management Inside Course (Add, Edit, Delete, Move Up / Down) -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-gray-100 gap-4">
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Course Lessons</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Manage and organize lessons. Use the arrows to reorder.</p>
                </div>
                <a href="{{ route('instructor.lessons.create', $course) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 shadow-sm transition">
                    + Add New Lesson
                </a>
            </div>

            @php
                $allLessons = $course->lessons()->orderBy('order')->orderBy('id')->get();
                $lessonCount = $allLessons->count();
            @endphp

            @if ($allLessons->isEmpty())
                <div class="p-12 text-center text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <p class="text-base font-bold text-gray-700">No lessons added to this course yet.</p>
                    <p class="text-xs text-gray-400 mt-1">Courses require at least one lesson before they can be submitted for administrator approval.</p>
                    <div class="mt-4">
                        <a href="{{ route('instructor.lessons.create', $course) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition">
                            + Add First Lesson
                        </a>
                    </div>
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($allLessons as $index => $lesson)
                        <li class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-gray-50 px-3 rounded-xl transition gap-3">
                            <div class="flex items-center space-x-3.5 min-w-0">
                                <span class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-gray-700 text-xs font-mono font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-bold text-gray-900 truncate">{{ $lesson->title }}</span>
                                        <span class="flex-shrink-0 text-xs uppercase px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold">{{ $lesson->content_type }}</span>
                                        @if ($lesson->is_preview)
                                            <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">Preview</span>
                                        @endif
                                    </div>
                                    @if ($lesson->duration_minutes)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $lesson->duration_minutes }} minutes</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Move Up, Move Down, Edit, and Delete Lesson Actions -->
                            <div class="flex items-center space-x-2 flex-shrink-0 self-end sm:self-auto">
                                <!-- Move Up Button -->
                                @if ($index > 0)
                                    <form method="POST" action="{{ route('instructor.lessons.move-up', $lesson) }}" class="inline">
                                        @csrf
                                        <button type="submit" title="Move Up"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition">
                                            &uarr;
                                        </button>
                                    </form>
                                @else
                                    <span class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-50 text-gray-300 text-xs cursor-not-allowed">
                                        &uarr;
                                    </span>
                                @endif

                                <!-- Move Down Button -->
                                @if ($index < $lessonCount - 1)
                                    <form method="POST" action="{{ route('instructor.lessons.move-down', $lesson) }}" class="inline">
                                        @csrf
                                        <button type="submit" title="Move Down"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition">
                                            &darr;
                                        </button>
                                    </form>
                                @else
                                    <span class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-50 text-gray-300 text-xs cursor-not-allowed">
                                        &darr;
                                    </span>
                                @endif

                                <span class="text-gray-200">|</span>

                                <!-- Edit Lesson -->
                                <a href="{{ route('instructor.lessons.edit', $lesson) }}"
                                   class="px-2.5 py-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 rounded transition">
                                    Edit
                                </a>

                                <!-- Delete Lesson with Confirmation Modal -->
                                <x-confirm-delete-modal
                                    :action="route('instructor.lessons.destroy', $lesson)"
                                    title="Delete Lesson"
                                    message="Are you sure you want to delete lesson '{{ $lesson->title }}'? This action cannot be undone."
                                    button-text="Delete"
                                    button-class="px-2.5 py-1 text-xs font-bold text-red-600 hover:text-red-800 rounded transition" />
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Course Details Form with Save as Draft and Submit for Approval buttons -->
        <form method="POST" action="{{ route('instructor.courses.update', $course) }}" enctype="multipart/form-data"
              class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <h2 class="text-xl font-black text-gray-900 tracking-tight pb-3 border-b border-gray-100">Course Metadata &amp; Content</h2>

            <div>
                <label for="title" class="block text-sm font-bold text-gray-700">Course Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $course->title) }}" required
                       class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="category_id" class="block text-sm font-bold text-gray-700">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required
                            class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $course->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="difficulty" class="block text-sm font-bold text-gray-700">Difficulty Level <span class="text-red-500">*</span></label>
                    <select name="difficulty" id="difficulty" required
                            class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="beginner" {{ old('difficulty', $course->difficulty) === 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ old('difficulty', $course->difficulty) === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ old('difficulty', $course->difficulty) === 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="estimated_duration_minutes" class="block text-sm font-bold text-gray-700">Estimated Duration (Minutes)</label>
                <input type="number" name="estimated_duration_minutes" id="estimated_duration_minutes" value="{{ old('estimated_duration_minutes', $course->estimated_duration_minutes) }}" min="1"
                       class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-bold text-gray-700">Course Description <span class="text-red-500">*</span></label>
                <textarea name="description" id="description" rows="4" required
                          class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $course->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Learning Objectives</label>
                @php
                    $objectives = old('learning_objectives', $course->learning_objectives ?? []);
                @endphp
                <input type="text" name="learning_objectives[]" placeholder="Objective 1" value="{{ $objectives[0] ?? '' }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm border p-2 text-sm mb-2 focus:ring-2 focus:ring-indigo-500">
                <input type="text" name="learning_objectives[]" placeholder="Objective 2" value="{{ $objectives[1] ?? '' }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm border p-2 text-sm mb-2 focus:ring-2 focus:ring-indigo-500">
                <input type="text" name="learning_objectives[]" placeholder="Objective 3" value="{{ $objectives[2] ?? '' }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm border p-2 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="thumbnail" class="block text-sm font-bold text-gray-700">Course Thumbnail Upload</label>
                @if ($course->thumbnail_path)
                    <div class="my-3">
                        <img src="{{ asset('storage/' . $course->thumbnail_path) }}" alt="Current thumbnail" class="w-40 h-24 object-cover rounded-xl border shadow-sm">
                    </div>
                @endif
                <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg,image/png,image/webp"
                       class="mt-1.5 block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
            </div>

            <!-- Form Action Buttons: Save as Draft and Submit for Approval -->
            <div class="flex flex-col sm:flex-row justify-end items-center gap-3 pt-6 border-t border-gray-100">
                <button type="submit" name="action" value="draft"
                        class="w-full sm:w-auto px-5 py-2.5 bg-gray-800 text-white rounded-xl text-sm font-bold hover:bg-gray-900 shadow-sm transition">
                    Save as Draft
                </button>
                <button type="submit" name="action" value="submit"
                        class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-sm transition">
                    Submit for Approval
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
