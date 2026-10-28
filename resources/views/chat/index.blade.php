<x-layouts.app>
    <x-slot:title>Messages & Discussions - Villapa LMS</x-slot:title>

    <div class="space-y-8">
        <x-page-header
            title="Messages & Discussions"
            subtitle="Subject group messaging, class channels, and direct communications."
        />

        {{-- Subject Group Chats: Only visible to students and instructors --}}
        @if (!auth()->user()->hasRole(['admin', 'super-admin']))
        <div class="bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 rounded-2xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-line/60">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gold/15 text-gold border border-gold/40">
                        <x-icon name="chat" class="h-4.5 w-4.5" />
                    </span>
                    <div>
                        <h2 class="font-serif text-lg sm:text-xl font-bold text-navy">Subject Group Chats</h2>
                        <p class="text-xs text-gray-500">
                            @if (auth()->user()->hasRole('instructor'))
                                Group discussions for your assigned subject offerings.
                            @else
                                Group discussions for your enrolled subjects.
                            @endif
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">
                    {{ $offerings->count() }} Active Rooms
                </span>
            </div>

            @if ($offerings->isEmpty())
                <div class="p-8 text-center bg-[#FAF8F2] rounded-xl border border-dashed border-line">
                    <p class="text-sm font-semibold text-navy">No subject group chats available.</p>
                    <p class="text-xs text-gray-500 mt-1">
                        @if (auth()->user()->hasRole('instructor'))
                            You will see group chats once you are assigned as an instructor to a subject offering.
                        @else
                            You will automatically join group discussions once an administrator enrolls you in your subjects.
                        @endif
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($offerings as $offering)
                        @php
                            $subject = $offering->subject;
                            $instructorName = $offering->instructor?->name ?: ($offering->instructor_name ?: ($subject?->instructor ?? 'Faculty'));
                        @endphp
                        <div class="flex flex-col justify-between rounded-xl border border-line/80 bg-white p-5 hover:border-gold/60 transition shadow-2xs">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <span class="inline-flex items-center rounded-lg bg-gold/15 px-2.5 py-0.5 font-mono text-xs font-bold text-gold border border-gold/30">
                                        {{ $subject?->subject_code ?? 'SUBJ' }}
                                    </span>
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">
                                        {{ $offering->section?->name ?? 'Section' }}
                                    </span>
                                </div>
                                <h3 class="font-serif text-base font-bold text-navy leading-snug line-clamp-2">
                                    {{ $subject?->title ?? 'Subject Offering Room' }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-1 truncate">
                                    Instructor: {{ $instructorName }}
                                </p>
                                @if ($offering->academicTerm)
                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        Term: {{ $offering->academicTerm->name }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-4 pt-3 border-t border-line/50 flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Group Chat Active
                                </span>
                                <a href="{{ route('chat.offering', $offering) }}"
                                   class="inline-flex items-center gap-1 rounded-xl bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] px-3.5 py-1.5 text-xs font-bold text-white shadow-2xs hover:border-gold/60 border border-transparent transition">
                                    <span>Enter Chat</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endif

        {{-- Two-column: Direct Messages & Course Channels --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

            {{-- Direct Messages --}}
            <div class="bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 rounded-2xl p-6">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-line/60">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <x-icon name="user" class="h-4 w-4" />
                        </span>
                        <div>
                            <h2 class="font-serif text-base sm:text-lg font-bold text-navy">Direct Messages</h2>
                            <p class="text-xs text-gray-500">
                                @if (auth()->user()->hasRole(['admin', 'super-admin']))
                                    Send a private message to any active user.
                                @else
                                    1-on-1 private messaging with your professors and classmates.
                                @endif
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-gray-400 font-mono">{{ $contacts->count() }} contacts</span>
                </div>

                @if ($contacts->isEmpty())
                    <div class="p-6 text-center text-gray-500 bg-[#FAF8F2] rounded-xl border border-dashed border-line">
                        <p class="text-sm font-medium text-navy">No contacts available.</p>
                        <p class="text-xs text-gray-400 mt-1">
                            @if (auth()->user()->hasRole(['admin', 'super-admin']))
                                There are no other active users to message yet.
                            @else
                                Direct messaging is available with professors and peers in your enrolled subjects.
                            @endif
                        </p>
                    </div>
                @else
                    <ul class="divide-y divide-line/60 max-h-[520px] overflow-y-auto pr-1">
                        @foreach ($contacts as $contact)
                            <li class="py-3 flex justify-between items-center hover:bg-[#FAF8F2] px-2 rounded-xl transition">
                                <div class="flex items-center space-x-3">
                                    <div class="h-9 w-9 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-serif font-bold text-xs shadow-2xs flex-shrink-0">
                                        {{ strtoupper(substr($contact->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-serif font-bold text-navy truncate">{{ $contact->name }}</h3>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase
                                                {{ $contact->hasRole('instructor') ? 'bg-indigo-100 text-indigo-800' : ($contact->hasRole('admin') ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">
                                                {{ $contact->getPrimaryRole() }}
                                            </span>
                                            <span class="text-xs text-gray-400 truncate max-w-[130px]">{{ $contact->email }}</span>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('chat.direct', $contact) }}" class="ml-3 flex-shrink-0 px-3 py-1.5 border border-line bg-white text-navy font-semibold rounded-xl text-xs hover:border-gold hover:text-gold transition shadow-2xs">
                                    Message &rarr;
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Legacy Course Channels (only for non-admin) --}}
            @if ($courses->isNotEmpty())
                <div class="bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 rounded-2xl p-6">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-line/60">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <x-icon name="book" class="h-4 w-4" />
                            </span>
                            <div>
                                <h2 class="font-serif text-base sm:text-lg font-bold text-navy">Course Channels</h2>
                                <p class="text-xs text-gray-500">Program courses discussion boards.</p>
                            </div>
                        </div>
                    </div>

                    <ul class="divide-y divide-line/60 max-h-[520px] overflow-y-auto pr-1">
                        @foreach ($courses as $course)
                            <li class="py-3 flex justify-between items-center hover:bg-[#FAF8F2] px-2 rounded-xl transition">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-serif font-bold text-navy truncate">{{ $course->title }}</h3>
                                    <p class="text-xs text-gray-500">Instructor: {{ $course->instructor?->name ?? 'Faculty' }}</p>
                                </div>
                                <a href="{{ route('chat.course', $course) }}" class="ml-3 flex-shrink-0 px-3 py-1.5 bg-[#0A192F] text-gold border border-gold/40 rounded-xl text-xs font-semibold hover:bg-[#14365D] hover:text-white transition shadow-2xs">
                                    Open Room &rarr;
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
