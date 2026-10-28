<x-layouts.app>
    <x-slot:title>All Subjects - Administrator Console</x-slot:title>

    <div class="space-y-6">
        @if (session('status'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm text-emerald-800 text-sm font-semibold">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm text-red-800 text-sm font-semibold space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">All Institutional Subjects</h1>
                <p class="text-sm text-gray-500 mt-1">Review, monitor, preview, and unpublish subjects across all departments.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.courses.pending') }}" class="px-4 py-2 border border-amber-300 bg-amber-50 text-amber-800 rounded-md text-sm font-medium hover:bg-amber-100 transition">
                    Pending Approval Queue
                </a>
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 transition">
                    &larr; Admin Dashboard
                </a>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white shadow rounded-lg p-6">
            <form method="GET" action="{{ route('admin.courses.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Search Subject Title</label>
                    <input type="text" id="search" name="search" value="{{ $currentSearch }}" placeholder="Search subjects by title..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Publication Status</label>
                    <select id="status" name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">All Publication Statuses</option>
                        <option value="published" {{ $currentStatus === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="archived" {{ $currentStatus === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>

                <div>
                    <label for="approval" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Approval Status</label>
                    <select id="approval" name="approval" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">All Approvals</option>
                        <option value="approved" {{ $currentApproval === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ $currentApproval === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ $currentApproval === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="flex space-x-2 md:col-span-4 justify-end">
                    <a href="{{ route('admin.courses.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 transition">
                        Reset Filters
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 shadow-sm transition">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Courses Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if ($courses->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No courses found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria or status filters.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Instructor</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Publication</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Approval</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Stats</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($courses as $course)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $course->title }}</div>
                                        <div class="text-xs text-gray-500 capitalize">{{ $course->difficulty }} level</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $course->instructor->name ?? 'Unassigned' }}</div>
                                        <div class="text-xs text-gray-500">{{ $course->instructor->email ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                            {{ $course->category->name ?? 'None' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($course->publication_status === 'published')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                Published
                                            </span>
                                        @elseif ($course->publication_status === 'draft')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Draft
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                {{ ucfirst($course->publication_status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($course->approval_status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Approved
                                            </span>
                                        @elseif ($course->approval_status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                Pending Review
                                            </span>
                                        @elseif ($course->approval_status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ ucfirst($course->approval_status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-600">
                                        <div><span class="font-bold">{{ $course->lessons_count }}</span> lessons</div>
                                        <div><span class="font-bold">{{ $course->enrollments_count }}</span> students</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <!-- Preview Link -->
                                        <a href="{{ route('courses.show', $course) }}" target="_blank" class="px-2.5 py-1 border border-gray-300 text-gray-700 rounded text-xs font-medium hover:bg-gray-50 transition inline-flex items-center">
                                            Preview
                                        </a>

                                        <!-- Unpublish Action with Alpine Confirmation Modal -->
                                        @if ($course->publication_status === 'published')
                                            <div x-data="{ open: false }" class="inline">
                                                <button type="button" @click="open = true" class="px-2.5 py-1 border border-amber-300 bg-amber-50 text-amber-800 rounded text-xs font-medium hover:bg-amber-100 transition inline-flex items-center">
                                                    Unpublish
                                                </button>

                                                <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                        <div x-show="open" class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="open = false"></div>
                                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                                        <div x-show="open" class="relative inline-block align-bottom bg-white rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                            <div class="sm:flex sm:items-start">
                                                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10 text-amber-600">
                                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                    </svg>
                                                                </div>
                                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                                    <h3 class="text-lg font-bold text-gray-900">Confirm Course Unpublish</h3>
                                                                    <p class="mt-2 text-sm text-gray-500">
                                                                        Are you sure you want to unpublish <strong>"{{ $course->title }}"</strong>? It will immediately be removed from the public student catalog and set back to draft status.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                                                                <form method="POST" action="{{ route('admin.courses.unpublish', $course) }}" class="inline">
                                                                    @csrf
                                                                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-sm font-bold text-white hover:bg-amber-700 transition sm:w-auto">
                                                                        Yes, Unpublish Course
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
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $courses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
