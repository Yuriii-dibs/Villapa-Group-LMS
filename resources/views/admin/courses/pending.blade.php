<x-layouts.app>
    <x-slot:title>Pending Subject Approvals - Administrator</x-slot:title>

    <div class="space-y-6">
        @if (session('status'))
            <div class="p-4 bg-green-50 border-l-4 border-green-400 text-green-700 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-400 text-red-700 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="flex justify-between items-center bg-white shadow rounded-lg p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pending Subject Approvals</h1>
                <p class="text-sm text-gray-500 mt-1">Review newly submitted subjects before publishing them to the public catalog.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                &larr; Admin Dashboard
            </a>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if ($pendingCourses->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    <p class="text-base font-medium">No subjects currently awaiting review.</p>
                    <p class="text-xs text-gray-400 mt-1">All submitted instructor subjects have been processed.</p>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach ($pendingCourses as $course)
                        <div class="p-6 space-y-4">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div>
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                        {{ $course->category->name }}
                                    </span>
                                    <h2 class="text-xl font-bold text-gray-900 mt-1">{{ $course->title }}</h2>
                                    <p class="text-xs text-gray-500">
                                        Instructor: <span class="font-medium text-gray-800">{{ $course->instructor->name }}</span> ({{ $course->instructor->email }}) &bull; {{ $course->lessons_count }} lessons
                                    </p>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <!-- Preview Link -->
                                    <a href="{{ route('courses.show', $course) }}" target="_blank" class="px-3 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 shadow-sm transition">
                                        Preview Course &nearr;
                                    </a>

                                    <!-- Approve Form with Modal -->
                                    <div x-data="{ open: false }" class="inline">
                                        <button type="button" @click="open = true" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-medium hover:bg-emerald-700 shadow-sm transition">
                                            <x-icon name="check" class="h-4 w-4" /> Approve & Publish
                                        </button>

                                        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                <div x-show="open" class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="open = false"></div>
                                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                                <div x-show="open" class="relative inline-block align-bottom bg-white rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                    <div class="sm:flex sm:items-start">
                                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10 text-emerald-600">
                                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3 class="text-lg font-bold text-gray-900">Approve & Publish Course</h3>
                                                            <p class="mt-2 text-sm text-gray-500">
                                                                Are you sure you want to approve <strong>"{{ $course->title }}"</strong>? It will immediately become visible to all students in the public course catalog.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                                                        <form method="POST" action="{{ route('admin.courses.approve', $course) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-sm font-bold text-white hover:bg-emerald-700 transition sm:w-auto">
                                                                Confirm & Publish
                                                            </button>
                                                        </form>
                                                        <button type="button" @click="open = false" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition sm:w-auto">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 bg-gray-50 p-4 rounded border border-gray-100">
                                {{ $course->description }}
                            </p>

                            <!-- Reject Form -->
                            <details class="group bg-gray-50 p-4 rounded border border-gray-200">
                                <summary class="cursor-pointer text-sm font-semibold text-red-600 hover:text-red-800 select-none">
                                    Reject Course & Request Modifications...
                                </summary>
                                <form method="POST" action="{{ route('admin.courses.reject', $course) }}" class="mt-3 space-y-3">
                                    @csrf
                                    <div>
                                        <label for="reason_{{ $course->id }}" class="block text-xs font-medium text-gray-700">Rejection Reason / Required Changes (Required)</label>
                                        <textarea name="reason" id="reason_{{ $course->id }}" rows="3" required placeholder="Specify why the course cannot be approved and what needs revision..."
                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 text-sm focus:border-red-500 focus:ring-red-500 @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                                        @error('reason')
                                            <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-xs font-medium hover:bg-red-700">
                                            Confirm Rejection
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </div>
                    @endforeach
                </div>
                <div class="p-4 border-t border-gray-200">
                    {{ $pendingCourses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
