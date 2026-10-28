<x-layouts.app>
    <x-slot:title>Attendance Log - {{ $session->title }}</x-slot:title>

    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <a href="{{ route('instructor.classes.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">&larr; Back to Sessions</a>
                <h1 class="text-xl font-bold text-gray-900 mt-1">Attendance Log: {{ $session->title }}</h1>
                <p class="text-xs text-gray-500">
                    Course: {{ $session->course->title }} &bull; Scheduled: {{ $session->starts_at->format('M d, Y g:i A') }}
                </p>
            </div>
            <div class="flex space-x-3 items-center">
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">
                    {{ $attendances->count() }} Recorded Attendees
                </span>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if ($attendances->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    <p class="text-sm font-medium">No attendees recorded for this session yet.</p>
                    <p class="text-xs text-gray-400 mt-1">Students will be logged automatically when they join the live class room.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Joined At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($attendances as $index => $attendance)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        {{ $attendance->user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ $attendance->user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                        {{ $attendance->joined_at->format('M d, Y h:i:s A') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
