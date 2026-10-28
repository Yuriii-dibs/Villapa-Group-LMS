<x-layouts.app>
    <x-slot:title>Live Class Sessions - Instructor Portal</x-slot:title>

    <div class="space-y-6">
        <div class="bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-serif font-semibold text-navy tracking-tight">Online Live Classes</h1>
                    <span class="hanko-seal text-[10px] px-1.5 py-0.5 tracking-wider hidden sm:inline-flex">授業</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Schedule and conduct live video classes via free embedded Jitsi or external conference links.</p>
            </div>
            <div>
                <a href="{{ route('instructor.classes.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white border border-gold/40 rounded-lg text-sm font-medium hover:border-gold/70 shadow-sm transition">
                    + Schedule Live Session
                </a>
            </div>
        </div>

        <div class="bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 rounded-xl overflow-hidden">
            @if ($sessions->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No scheduled class sessions</h3>
                    <p class="mt-1 text-sm text-gray-500">Schedule your first live video session for your students.</p>
                    <div class="mt-6">
                        <a href="{{ route('instructor.classes.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                            Schedule Session
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Schedule</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Provider</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Attendance</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($sessions as $session)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $session->title }}</div>
                                        @if ($session->description)
                                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ $session->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700">
                                        {{ $session->course->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        <div>{{ $session->starts_at->format('M d, Y') }}</div>
                                        <div>{{ $session->starts_at->format('g:i A') }} - {{ $session->ends_at->format('g:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold uppercase">
                                        <span class="px-2 py-0.5 rounded {{ $session->isJitsi() ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $session->provider }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($session->isLive())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 animate-pulse">
                                                ● LIVE
                                            </span>
                                        @elseif ($session->isEnded())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Ended
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Scheduled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <a href="{{ route('instructor.classes.attendance', $session) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                            {{ $session->attendances_count }} Attendees &rarr;
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-1">
                                        @if (! $session->isEnded())
                                            @if (! $session->isLive())
                                                <div x-data="{ open: false }" class="inline">
                                                    <button type="button" @click="open = true" class="px-2.5 py-1 bg-rose-600 text-white rounded text-xs font-semibold hover:bg-rose-700 transition">
                                                        Start Live
                                                    </button>
                                                    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                            <div x-show="open" class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="open = false"></div>
                                                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                                            <div x-show="open" class="relative inline-block align-bottom bg-white rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                                <div class="sm:flex sm:items-start">
                                                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10 text-rose-600">
                                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                                        </svg>
                                                                    </div>
                                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                                        <h3 class="text-lg font-bold text-gray-900">Start Live Class Session</h3>
                                                                        <p class="mt-2 text-sm text-gray-500">
                                                                            Are you ready to broadcast <strong>"{{ $session->title }}"</strong>? Enrolled students will immediately be able to join the room.
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                                                                    <form method="POST" action="{{ route('instructor.classes.start', $session) }}" class="inline">
                                                                        @csrf
                                                                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-sm font-bold text-white hover:bg-rose-700 transition sm:w-auto">
                                                                            Yes, Start Live Class
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
                                            @else
                                                <a href="{{ route('classes.show', $session) }}" class="px-2.5 py-1 bg-emerald-600 text-white rounded text-xs font-semibold hover:bg-emerald-700 transition">
                                                    Open Room
                                                </a>

                                                <div x-data="{ open: false }" class="inline">
                                                    <button type="button" @click="open = true" class="px-2.5 py-1 border border-gray-300 text-gray-700 rounded text-xs font-semibold hover:bg-gray-100 transition">
                                                        End Class
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
                                                                        <h3 class="text-lg font-bold text-gray-900">End Class Session</h3>
                                                                        <p class="mt-2 text-sm text-gray-500">
                                                                            Are you sure you want to end this live class session? Students will no longer be able to join.
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                                                                    <form method="POST" action="{{ route('instructor.classes.end', $session) }}" class="inline">
                                                                        @csrf
                                                                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-bold text-white hover:bg-red-700 transition sm:w-auto">
                                                                            Yes, End Session
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
                                        @endif

                                        <a href="{{ route('instructor.classes.edit', $session) }}" class="px-2 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">Edit</a>

                                        <x-confirm-delete-modal
                                            :action="route('instructor.classes.destroy', $session)"
                                            title="Cancel Class Session"
                                            message="Are you sure you want to cancel and delete this class session? All attendance records will be removed."
                                            button-text="Cancel Class"
                                            button-class="px-2 py-1 text-xs font-semibold text-red-600 hover:text-red-800 transition" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
