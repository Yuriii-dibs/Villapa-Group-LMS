<x-layouts.app>
    <x-slot:title>My Authored Subjects - Instructor Portal</x-slot:title>

    <div class="space-y-6">
        @if (session('status'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg text-sm shadow-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white shadow-sm border border-gray-200 rounded-2xl p-6 sm:p-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">My Subjects</h1>
                <p class="text-sm text-gray-500 mt-1">Manage syllabus content, monitor publication status, and view administrator feedback.</p>
            </div>
            <a href="{{ route('instructor.courses.create') }}"
               class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-sm transition">
                + Create New Subject
            </a>
        </div>

        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl overflow-hidden">
            @if ($courses->isEmpty())
                <div class="p-16 text-center text-gray-500">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <p class="text-base font-bold text-gray-800">You haven't authored any subjects yet.</p>
                    <p class="text-xs text-gray-400 mt-1">Click "+ Create New Subject" above to begin your first curriculum.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-500 tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 text-left">Subject Details</th>
                                <th class="px-6 py-3.5 text-left">Category</th>
                                <th class="px-6 py-3.5 text-left">Status</th>
                                <th class="px-6 py-3.5 text-left">Lessons</th>
                                <th class="px-6 py-3.5 text-left">Enrollments</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($courses as $course)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">
                                            <a href="{{ route('instructor.courses.edit', $course) }}" class="hover:text-indigo-600 transition">
                                                {{ $course->title }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5 capitalize">{{ $course->difficulty }} level</div>

                                        <!-- Show Rejection Reason when rejected -->
                                        @if ($course->approval_status === 'rejected')
                                            @php
                                                $rejectionReview = $course->reviews->firstWhere('decision', 'rejected');
                                            @endphp
                                            @if ($rejectionReview && $rejectionReview->reason)
                                                <div class="mt-2 text-xs bg-red-50 text-red-800 p-3 rounded-lg border border-red-200">
                                                    <span class="font-bold">Rejection Reason:</span>
                                                    <span class="ml-1">{{ $rejectionReview->reason }}</span>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-gray-600">
                                        {{ $course->category->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1 items-start">
                                            <!-- Approval Status Badge (draft, pending, published, rejected) -->
                                            @if ($course->publication_status === 'published')
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                    Published
                                                </span>
                                            @elseif ($course->approval_status === 'pending')
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                    Pending
                                                </span>
                                            @elseif ($course->approval_status === 'rejected')
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            @elseif ($course->approval_status === 'draft')
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                                    Draft
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                    Approved
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        {{ $course->lessons_count }} {{ Str::plural('lesson', $course->lessons_count) }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        <a href="{{ route('instructor.students.index', ['course_id' => $course->id]) }}" class="hover:text-indigo-600 font-semibold underline">
                                            {{ $course->enrollments_count }} {{ Str::plural('student', $course->enrollments_count) }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                        @if (in_array($course->approval_status, ['draft', 'rejected'], true))
                                            <form method="POST" action="{{ route('instructor.courses.submit', $course) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-emerald-600 hover:text-emerald-900 font-bold text-xs transition">
                                                    Submit for Approval
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('instructor.courses.edit', $course) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs transition">
                                            Edit
                                        </a>
                                        <!-- Delete Course with Confirmation Modal -->
                                        <x-confirm-delete-modal
                                            :action="route('instructor.courses.destroy', $course)"
                                            title="Delete Course"
                                            message="Are you sure you want to delete this course? All syllabus modules and lessons will also be removed."
                                            button-text="Delete"
                                            button-class="text-red-600 hover:text-red-900 font-bold text-xs transition" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $courses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
