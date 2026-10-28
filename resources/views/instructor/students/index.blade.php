<x-layouts.app>
    <x-slot:title>Enrolled Students - Instructor Portal</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white shadow-sm border border-gray-200 rounded-2xl p-6 sm:p-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Enrolled Students</h1>
                <p class="text-sm text-gray-500 mt-1">Monitor learner progress and curriculum completion across all your authored subjects.</p>
            </div>
            <a href="{{ route('instructor.courses.index') }}"
               class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-200 transition">
                &larr; Back to Subjects
            </a>
        </div>

        <!-- Filter & Search Toolbar -->
        <form method="GET" action="{{ route('instructor.students.index') }}" class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Search Student</label>
                <input type="text" name="search" id="search" value="{{ $currentSearch }}" placeholder="Name or email address..."
                       class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="course_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Filter by Subject</label>
                <select name="course_id" id="course_id"
                        class="mt-1.5 block w-full rounded-xl border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All My Subjects</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}" {{ $currentCourseId == $c->id ? 'selected' : '' }}>
                            {{ $c->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-sm transition">
                    Apply Filter
                </button>
                @if ($currentSearch || $currentCourseId)
                    <a href="{{ route('instructor.students.index') }}" class="py-2.5 px-4 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Students List Table -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl overflow-hidden">
            @if ($enrollments->isEmpty())
                <div class="p-16 text-center text-gray-500">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 text-gray-400 mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <p class="text-base font-bold text-gray-800">No students found matching your criteria</p>
                    <p class="text-xs text-gray-400 mt-1">Try clearing filters or search terms.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-500 tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 text-left">Student</th>
                                <th class="px-6 py-3.5 text-left">Course</th>
                                <th class="px-6 py-3.5 text-left">Enrolled Date</th>
                                <th class="px-6 py-3.5 text-left">Status</th>
                                <th class="px-6 py-3.5 text-left">Progress</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($enrollments as $enrollment)
                                @php
                                    $progress = $enrollment->progressPercentage();
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $enrollment->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $enrollment->user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $enrollment->course->title }}</div>
                                        <span class="inline-block mt-0.5 text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold">
                                            {{ $enrollment->course->category->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($enrollment->completed_at)
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                Completed ({{ $enrollment->completed_at->format('M d') }})
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                                In Progress
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap w-48">
                                        <div class="flex justify-between text-xs font-bold text-gray-700 mb-1">
                                            <span>{{ $progress }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('chat.direct', $enrollment->user_id) }}"
                                           class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                            Message &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $enrollments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
