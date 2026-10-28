<x-layouts.app>
    <x-slot:title>Create Subject - Instructor Portal</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Create New Subject</h1>
                <p class="text-sm text-gray-500 mt-1">Fill in the subject details below. You can save as a draft or submit for review once ready.</p>
            </div>
            <a href="{{ route('instructor.courses.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-800 transition">
                &larr; Back to Subjects
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-red-800 text-sm shadow-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('instructor.courses.store') }}" enctype="multipart/form-data" class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6">
            @csrf

            <div>
                <label for="title" class="block text-sm font-bold text-gray-700">Subject Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required autofocus
                       placeholder="e.g. CC 106 - App Dev. And Emerging Technologies"
                       class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="category_id" class="block text-sm font-bold text-gray-700">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required
                            class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="difficulty" class="block text-sm font-bold text-gray-700">Difficulty Level <span class="text-red-500">*</span></label>
                    <select name="difficulty" id="difficulty" required
                            class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="beginner" {{ old('difficulty') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ old('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ old('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="estimated_duration_minutes" class="block text-sm font-bold text-gray-700">Estimated Duration (Minutes)</label>
                <input type="number" name="estimated_duration_minutes" id="estimated_duration_minutes" value="{{ old('estimated_duration_minutes', 60) }}" min="1"
                       class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-bold text-gray-700">Course Description <span class="text-red-500">*</span></label>
                <textarea name="description" id="description" rows="4" required
                          placeholder="Provide a detailed overview of what students will accomplish in this curriculum..."
                          class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Key Learning Objectives</label>
                <p class="text-xs text-gray-400 mb-2">Bullet points displayed on the public catalog page.</p>
                <input type="text" name="learning_objectives[]" placeholder="Objective 1: Understand core architecture" value="{{ old('learning_objectives.0') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm border p-2 text-sm mb-2 focus:ring-2 focus:ring-indigo-500">
                <input type="text" name="learning_objectives[]" placeholder="Objective 2: Build end-to-end features" value="{{ old('learning_objectives.1') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm border p-2 text-sm mb-2 focus:ring-2 focus:ring-indigo-500">
                <input type="text" name="learning_objectives[]" placeholder="Objective 3: Deploy verified projects" value="{{ old('learning_objectives.2') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm border p-2 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="thumbnail" class="block text-sm font-bold text-gray-700">Course Thumbnail Upload</label>
                <p class="text-xs text-gray-400 mb-2">Recommended resolution 1280x720 (Max 2MB, JPG/PNG/WebP).</p>
                <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
            </div>

            <!-- Form Action Buttons: Save as Draft and Submit for Approval -->
            <div class="flex flex-col sm:flex-row justify-end items-center gap-3 pt-6 border-t border-gray-100">
                <a href="{{ route('instructor.courses.index') }}"
                   class="w-full sm:w-auto px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 text-center transition">
                    Cancel
                </a>
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
