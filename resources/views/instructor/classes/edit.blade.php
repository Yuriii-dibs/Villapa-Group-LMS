<x-layouts.app>
    <x-slot:title>Edit Online Class - Villapa LMS</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h1 class="text-xl font-bold text-gray-900">Edit Class Session</h1>
            <p class="text-sm text-gray-500 mt-1">Update session details for {{ $session->course->title }}.</p>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('instructor.classes.update', $session) }}" class="space-y-4" x-data="{ provider: '{{ $session->provider }}' }">
                @csrf
                @method('PUT')

                <div>
                    <label for="title" class="block text-xs font-semibold text-gray-700 uppercase">Session Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $session->title) }}" required class="mt-1 w-full border-gray-300 rounded-md text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-gray-700 uppercase">Description (Optional)</label>
                    <textarea id="description" name="description" rows="3" class="mt-1 w-full border-gray-300 rounded-md text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $session->description) }}</textarea>
                    @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="starts_at" class="block text-xs font-semibold text-gray-700 uppercase">Starts At</label>
                        <input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at', $session->starts_at->format('Y-m-d\TH:i')) }}" required class="mt-1 w-full border-gray-300 rounded-md text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('starts_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ends_at" class="block text-xs font-semibold text-gray-700 uppercase">Ends At</label>
                        <input type="datetime-local" id="ends_at" name="ends_at" value="{{ old('ends_at', $session->ends_at->format('Y-m-d\TH:i')) }}" required class="mt-1 w-full border-gray-300 rounded-md text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('ends_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Video Provider</label>
                    <div class="flex space-x-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="provider" value="jitsi" x-model="provider" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700 font-medium">Jitsi Meet</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="provider" value="external" x-model="provider" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700 font-medium">External Meeting Link</span>
                        </label>
                    </div>
                </div>

                <div x-show="provider === 'external'" x-cloak>
                    <label for="meeting_url" class="block text-xs font-semibold text-gray-700 uppercase">Meeting URL</label>
                    <input type="url" id="meeting_url" name="meeting_url" value="{{ old('meeting_url', $session->meeting_url) }}" class="mt-1 w-full border-gray-300 rounded-md text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('meeting_url') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('instructor.classes.index') }}" class="px-4 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700 shadow-sm">
                        Update Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
