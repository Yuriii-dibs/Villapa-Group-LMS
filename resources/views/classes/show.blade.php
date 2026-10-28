<x-layouts.app>
    <x-slot:title>Live Class: {{ $session->title }}</x-slot:title>

    <div class="space-y-4">
        <!-- Live Class Top Banner -->
        <div class="bg-white shadow rounded-lg p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase
                        {{ $session->isLive() ? 'bg-rose-100 text-rose-800 animate-pulse' : 'bg-blue-100 text-blue-800' }}">
                        {{ $session->status }}
                    </span>
                    <h1 class="text-lg font-bold text-gray-900">{{ $session->title }}</h1>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                    Course: <span class="font-medium text-gray-700">{{ $session->course->title }}</span>
                    &bull; Scheduled: {{ $session->starts_at->format('M d, g:i A') }} - {{ $session->ends_at->format('g:i A') }}
                </p>
            </div>

            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded font-medium border border-emerald-200">
                    <x-icon name="check" class="h-3.5 w-3.5 mr-1" /> Attendance Logged
                </span>

                @if ($isInstructor && $session->isLive())
                    <form method="POST" action="{{ route('instructor.classes.end', $session) }}" onsubmit="return confirm('End this live class?');" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded text-xs font-semibold hover:bg-red-700 transition shadow-sm">
                            End Class
                        </button>
                    </form>
                @endif

                <a href="{{ route('courses.index') }}" class="px-3 py-1.5 border border-gray-300 text-gray-700 rounded text-xs font-semibold hover:bg-gray-50 transition shadow-sm">
                    Leave Class
                </a>
            </div>
        </div>

        <!-- Meeting Screen Area -->
        <div class="bg-gray-900 rounded-xl shadow-2xl overflow-hidden" style="height: 680px;">
            @if ($session->isJitsi())
                <iframe src="https://meet.jit.si/lms-{{ $session->room_code }}#userInfo.displayName={{ urlencode($user->name) }}"
                        allow="camera; microphone; fullscreen; display-capture; autoplay"
                        class="w-full h-full border-0">
                </iframe>
            @else
                <div class="h-full flex flex-col items-center justify-center p-8 text-center text-white space-y-4">
                    <svg class="w-16 h-16 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <h2 class="text-2xl font-bold">External Video Conference</h2>
                    <p class="text-sm text-gray-300 max-w-md">
                        This session is hosted on an external video conferencing service. Your attendance has been logged automatically.
                    </p>
                    <a href="{{ $session->meeting_url }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="mt-4 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-lg text-sm inline-flex items-center">
                        Open Meeting in New Tab &rarr;
                    </a>
                </div>
            @endif
        </div>

        @if ($isInstructor)
            <!-- Instructor Attendance Table -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Session Attendance Roster</h2>
                        <p class="text-xs text-gray-500">Live roster of students who have joined this session.</p>
                    </div>
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-bold">
                        {{ $session->attendances()->count() }} Students Attended
                    </span>
                </div>

                @php
                    $liveAttendances = $session->attendances()->with('user')->orderBy('joined_at', 'asc')->get();
                @endphp

                @if ($liveAttendances->isEmpty())
                    <p class="text-xs text-gray-400 italic py-4 text-center">No students have joined yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">#</th>
                                    <th class="px-4 py-2.5 text-left">Student Name</th>
                                    <th class="px-4 py-2.5 text-left">Email</th>
                                    <th class="px-4 py-2.5 text-right">Joined At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($liveAttendances as $idx => $att)
                                    <tr>
                                        <td class="px-4 py-2.5 text-xs text-gray-400">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-2.5 font-bold text-gray-900 text-xs">{{ $att->user->name }}</td>
                                        <td class="px-4 py-2.5 text-xs text-gray-500">{{ $att->user->email }}</td>
                                        <td class="px-4 py-2.5 text-xs text-gray-600 text-right">{{ $att->joined_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts.app>
