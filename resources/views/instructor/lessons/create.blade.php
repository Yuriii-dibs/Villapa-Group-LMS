<x-layouts.app>
    <x-slot:title>Add Lesson - {{ $course->title }}</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Add New Lesson</h1>
                <p class="text-xs text-gray-500 mt-1">Course: {{ $course->title }}</p>
            </div>
            <a href="{{ route('instructor.courses.edit', $course) }}" class="text-sm text-gray-600 hover:text-gray-900">
                Cancel
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-400 text-red-700 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('instructor.lessons.store', $course) }}" class="bg-white shadow rounded-lg p-6 space-y-6">
            @csrf

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Lesson Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="content_type" class="block text-sm font-medium text-gray-700">Content Modality</label>
                    <select name="content_type" id="content_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm">
                        <option value="text" {{ old('content_type') === 'text' ? 'selected' : '' }}>Text / Article</option>
                        <option value="video" {{ old('content_type') === 'video' ? 'selected' : '' }}>Video (Embed)</option>
                        <option value="pdf" {{ old('content_type') === 'pdf' ? 'selected' : '' }}>PDF Document</option>
                        <option value="external_link" {{ old('content_type') === 'external_link' ? 'selected' : '' }}>External Web Link</option>
                    </select>
                </div>

                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Duration (Minutes)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', 15) }}" min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm">
                </div>

                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700">Lesson Order Position</label>
                    <input type="number" name="order" id="order" value="{{ old('order', $course->lessons->count() + 1) }}" min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_preview" id="is_preview" value="1" {{ old('is_preview') ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="is_preview" class="ml-2 block text-sm text-gray-700">
                    Allow Free Preview (Students can view this lesson without enrolling)
                </label>
            </div>

            <div>
                <label for="video_url" class="block text-sm font-medium text-gray-700">Video Embed URL (if applicable)</label>
                <input type="url" name="video_url" id="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/embed/..."
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm">
            </div>

            <div>
                <label for="external_url" class="block text-sm font-medium text-gray-700">External Resource URL (if applicable)</label>
                <input type="url" name="external_url" id="external_url" value="{{ old('external_url') }}" placeholder="https://..."
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm">
            </div>

            <div>
                <label for="body" class="block text-sm font-medium text-gray-700">Lesson Text Content</label>
                <textarea name="body" id="body" rows="8" placeholder="Type lecture notes or learning material here..."
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm font-mono">{{ old('body') }}</textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="{{ route('instructor.courses.edit', $course) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                    Save Lesson
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
