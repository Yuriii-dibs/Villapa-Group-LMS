<x-layouts.app>
    <x-slot:title>{{ $offering->subject?->subject_code }} ({{ $offering->section?->name }}) – Class Roster</x-slot:title>

    @php
        $subj = $offering->subject;
    @endphp

    <div class="space-y-8">
        {{-- Header --}}
        <div class="rounded-2xl bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] p-6 text-paper shadow-md border border-gold/30">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center rounded-lg bg-gold/20 px-3 py-1 font-mono text-xs font-bold text-gold border border-gold/40">
                            {{ $subj?->subject_code }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-gray-200">
                            Section: {{ $offering->section?->name }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-gray-200">
                            {{ $offering->academicTerm?->name }}
                        </span>
                    </div>
                    <h1 class="font-serif text-xl sm:text-2xl font-bold text-white tracking-tight">
                        {{ $subj?->title }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('instructor.offerings.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-white/20 bg-white/10 px-3.5 py-2 text-xs font-semibold text-white hover:bg-white/20 transition">
                        <span>&larr; Back to Classes</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Main Grid: Meet Link & Details (Left) + Student Roster (Right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Left column: Meet Link and Schedules --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Edit Google Meet Link --}}
                <div class="rounded-2xl bg-[#FFFEFA] border border-line p-5 shadow-2xs space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <x-icon name="video" class="h-4 w-4" />
                        </span>
                        <h2 class="font-serif text-sm font-bold text-navy">Google Meet Session</h2>
                    </div>

                    <form method="POST" action="{{ route('instructor.offerings.update-meet-link', $offering) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Meet Video Link</label>
                            <input type="url" name="meet_link" value="{{ old('meet_link', $offering->meet_link) }}"
                                   placeholder="https://meet.google.com/xxx-yyyy-zzz"
                                   class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                            <p class="text-[11px] text-gray-400 mt-1">
                                Enrolled students will see the Join Google Meet button 15 minutes before scheduled class time.
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            @if ($offering->meet_link)
                                <a href="{{ $offering->meet_link }}" target="_blank" rel="noopener noreferrer"
                                   class="text-xs font-bold text-emerald-600 hover:underline">
                                    Test Link &rarr;
                                </a>
                            @else
                                <span></span>
                            @endif
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0A192F] hover:bg-[#14365D] text-gold text-xs font-bold border border-gold/40 transition">
                                <x-icon name="check" class="h-3.5 w-3.5" />
                                <span>Save Link</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Schedule Details --}}
                <div class="rounded-2xl bg-[#FFFEFA] border border-line p-5 shadow-2xs space-y-3">
                    <h3 class="font-serif text-sm font-bold text-navy">Weekly Teaching Schedules</h3>
                    <div class="space-y-2">
                        @php
                            $schedules = $offering->schedules ?: ($subj?->schedules ?: []);
                        @endphp
                        @forelse ($schedules as $slot)
                            <div class="p-3 rounded-xl bg-gray-50 border border-line/60 text-xs space-y-1">
                                <div class="flex items-center justify-between font-bold text-navy">
                                    <span>{{ $slot['day'] ?? '' }}</span>
                                    <span>{{ $slot['type'] ?? 'Lec' }}</span>
                                </div>
                                <div class="text-gray-500">
                                    {{ $slot['time_start'] ?? '' }} &ndash; {{ $slot['time_end'] ?? '' }}
                                </div>
                                <div class="text-[11px] text-gray-400">
                                    Room: {{ $slot['room'] ?? $offering->room ?? 'TBA' }}
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">No schedule slots configured.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Quick Link to Assignments --}}
                <div class="rounded-2xl bg-[#FFFEFA] border border-line p-5 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-serif text-sm font-bold text-navy">Subject Assignments</h3>
                        <a href="{{ route('instructor.assignments.create') }}?subject_id={{ $subj?->id }}" class="text-xs font-bold text-indigo-700 hover:text-navy">
                            + New &rarr;
                        </a>
                    </div>
                    <div class="space-y-2">
                        @forelse ($subj?->assignments ?? [] as $assignment)
                            <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-gray-50 border border-line/60">
                                <span class="font-medium text-navy truncate max-w-[170px]">{{ $assignment->title }}</span>
                                <a href="{{ route('instructor.assignments.submissions', $assignment) }}" class="font-bold text-indigo-700 hover:underline">
                                    Submissions &rarr;
                                </a>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">No assignments created yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Right column: Enrolled Students Roster (8 cols) --}}
            <div class="lg:col-span-8 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                            <x-icon name="users" class="h-4 w-4" />
                        </span>
                        <h2 class="font-serif text-lg font-bold text-navy tracking-tight">
                            Enrolled Student Roster ({{ $offering->students->count() }})
                        </h2>
                    </div>
                </div>

                @if ($offering->students->isEmpty())
                    <div class="p-12 text-center text-gray-500 bg-[#FFFEFA] rounded-2xl border border-dashed border-line shadow-2xs">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 mb-3">
                            <x-icon name="users" class="h-6 w-6" />
                        </div>
                        <p class="text-base font-bold text-navy">No students enrolled yet</p>
                        <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                            The administrator can enroll the whole {{ $offering->section?->name }} section or individual irregular students.
                        </p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl border border-line bg-[#FFFEFA] shadow-2xs">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50/80 border-b border-line text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                <tr>
                                    <th class="px-5 py-3.5">#</th>
                                    <th class="px-5 py-3.5">Student Name</th>
                                    <th class="px-5 py-3.5">Email Address</th>
                                    <th class="px-5 py-3.5">Enrolled Date</th>
                                    <th class="px-5 py-3.5 text-right">Message</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($offering->students as $index => $student)
                                    <tr class="hover:bg-indigo-50/20 transition">
                                        <td class="px-5 py-3.5 text-xs text-gray-400 font-mono">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-5 py-3.5 font-bold text-navy">
                                            {{ $student->name }}
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-gray-600 font-mono">
                                            {{ $student->email }}
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-gray-400">
                                            {{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('M d, Y') : 'Active' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <a href="{{ route('chat.direct', $student->id) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-[#0A192F] hover:text-gold text-navy text-xs font-semibold border border-line transition shadow-2xs">
                                                <x-icon name="chat" class="h-3 w-3" />
                                                <span>Chat</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-layouts.app>
