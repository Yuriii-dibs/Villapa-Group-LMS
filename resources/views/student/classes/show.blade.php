<x-layouts.app>
    <x-slot:title>{{ $subject->subject_code }} – {{ $subject->title }} – UEP LMS</x-slot:title>

    {{-- Breadcrumb Navigation --}}
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('student.classes.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-navy transition">
            <x-icon name="arrow" class="h-4 w-4 rotate-180" /> Back to My Classes
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-[#0A192F] px-3 py-1 text-xs font-bold text-gold border border-gold/40 shadow-xs">
                {{ $subject->program }}
            </span>
            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                Block {{ $subject->block ?? '1' }}
            </span>
        </div>
    </div>

    {{-- Session Status Alert --}}
    @if (session('status'))
        <div class="mb-6 flex items-center justify-between rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">
            <div class="flex items-center gap-3">
                <x-icon name="check" class="h-5 w-5 text-emerald-600 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-800 shadow-sm">
            @foreach ($errors->all() as $error)
                <p>&bull; {{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ── SUBJECT HERO BANNER ─────────────────────────────────────────────── --}}
    <div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] p-6 sm:p-8 text-paper shadow-[0_12px_32px_-8px_rgba(10,25,47,0.25)] border border-gold/40">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="max-w-2xl">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center rounded-lg bg-gold/15 px-3 py-1 font-mono text-xs font-bold text-gold border border-gold/40">
                        {{ $subject->subject_code }}
                    </span>
                    <span class="text-xs text-indigo-200">Year {{ $subject->year_level }} &bull; Section {{ $subject->section ?? 'A' }}</span>
                </div>
                <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-paper leading-tight">
                    {{ $subject->title }}
                </h1>
                <p class="mt-2 text-sm text-indigo-100/90 leading-relaxed">
                    {{ $subject->description ?? 'Official academic course curriculum for ' . $subject->program . '.' }}
                </p>
                <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-indigo-200 font-medium">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="users" class="h-4 w-4 text-gold" />
                        Instructor: <strong class="text-paper">{{ $subject->instructor ?? 'Faculty In-Charge' }}</strong>
                    </span>
                </div>
            </div>

            {{-- Direct Google Meet Join Card --}}
            <div class="rounded-2xl border border-emerald-400/40 bg-emerald-950/60 backdrop-blur-md p-5 text-center min-w-[240px] shadow-lg">
                <div class="inline-flex items-center gap-2 mb-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="text-xs font-bold tracking-wider text-emerald-300 uppercase">Live Classroom</span>
                </div>
                <p class="text-xs text-emerald-100/80 mb-3">Google Meet Class Room</p>
                <a href="{{ $subject->google_meet_url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-5 py-3 text-sm font-bold text-white shadow transition transform hover:scale-105">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
                    </svg>
                    Join Google Meet
                </a>
                <span class="block text-[10px] text-emerald-200/60 mt-2 font-mono truncate">
                    {{ $subject->google_meet_url }}
                </span>
            </div>
        </div>
        <img src="{{ asset('images/waves/wave-panel.svg') }}" alt="" class="pointer-events-none absolute inset-x-0 bottom-0 h-16 w-full opacity-35 object-cover object-bottom">
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- ── LEFT / MAIN CONTENT: ASSIGNMENTS & ACTIVITIES ───────────────── --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Assignments Section --}}
            <section class="bg-white rounded-2xl border border-line/80 p-6 sm:p-8 shadow-[0_2px_14px_rgba(10,25,47,0.03)]">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-line/60">
                    <div>
                        <div class="flex items-center gap-2">
                            <x-icon name="document-text" class="h-5 w-5 text-gold shrink-0" />
                            <h2 class="font-serif text-xl font-bold text-navy">Activities & Assignments</h2>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Submit your laboratory outputs, case studies, and activities here.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">
                        {{ $subject->assignments->count() }} Available
                    </span>
                </div>

                @if ($subject->assignments->isEmpty())
                    <x-empty-state
                        title="No assignments posted yet"
                        text="Your instructor hasn't posted any activities or assignments for this subject."
                    />
                @else
                    <div class="space-y-6">
                        @foreach ($subject->assignments as $assignment)
                            @php
                                $submission = $assignment->submissions->first();
                                $isSubmitted = $submission !== null;
                            @endphp

                            <div class="rounded-2xl border {{ $isSubmitted ? 'border-emerald-200 bg-emerald-50/20' : 'border-line/90 bg-[#FFFEFA]' }} p-6 shadow-sm transition hover:border-gold/50">
                                
                                {{-- Assignment Header --}}
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            @if ($isSubmitted)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-200">
                                                    <x-icon name="check" class="h-3 w-3" /> Passed / Submitted
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-800 border border-amber-200">
                                                    <x-icon name="clock" class="h-3 w-3" /> Pending Submission
                                                </span>
                                            @endif
                                            <span class="text-xs font-semibold text-gray-500">
                                                Points: {{ $assignment->total_points }} pts
                                            </span>
                                        </div>
                                        <h3 class="font-serif text-lg font-bold text-navy">
                                            {{ $assignment->title }}
                                        </h3>
                                    </div>

                                    @if ($assignment->due_date)
                                        <div class="text-left sm:text-right shrink-0">
                                            <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Due Date</span>
                                            <span class="text-xs font-semibold text-navy">{{ $assignment->due_date->format('M d, Y – g:i A') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Description & Instructions --}}
                                @if ($assignment->description)
                                    <p class="text-sm text-gray-700 leading-relaxed mb-3">
                                        {{ $assignment->description }}
                                    </p>
                                @endif

                                @if ($assignment->instructions)
                                    <div class="rounded-xl bg-gray-50 p-4 border border-line/60 mb-5">
                                        <span class="text-[11px] uppercase font-bold tracking-wider text-gray-500 block mb-1">Instructions:</span>
                                        <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed font-mono">
                                            {{ $assignment->instructions }}
                                        </p>
                                    </div>
                                @endif

                                {{-- SUBMISSION STATUS / SUBMIT FORM --}}
                                <div class="border-t border-line/70 pt-4 mt-4">
                                    @if ($isSubmitted)
                                        {{-- Submitted Details Banner --}}
                                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 mb-4">
                                            <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-emerald-700 font-bold text-sm">Output Submitted</span>
                                                    <span class="text-xs text-gray-500">&bull; {{ $submission->submitted_at->diffForHumans() }} ({{ $submission->submitted_at->format('M d, Y g:i A') }})</span>
                                                </div>
                                                @if ($submission->grade !== null)
                                                    <span class="inline-flex items-center rounded-lg bg-emerald-700 px-2.5 py-1 text-xs font-bold text-white">
                                                        Grade: {{ $submission->grade }} / {{ $assignment->total_points }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($submission->file_path)
                                                <div class="flex items-center justify-between rounded-lg bg-white p-3 border border-emerald-100">
                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                        <x-icon name="document-text" class="h-4 w-4 text-gray-500 shrink-0" />
                                                        <span class="text-xs font-medium text-navy truncate">{{ $submission->file_name ?? 'Submitted File' }}</span>
                                                    </div>
                                                    <a href="{{ asset('storage/' . $submission->file_path) }}"
                                                       target="_blank"
                                                       class="text-xs font-bold text-indigo-600 hover:text-navy underline">
                                                        View File
                                                    </a>
                                                </div>
                                            @endif

                                            @if ($submission->notes)
                                                <div class="mt-2 text-xs text-gray-600 italic">
                                                    <strong>Remarks:</strong> {{ $submission->notes }}
                                                </div>
                                            @endif

                                            @if ($submission->feedback)
                                                <div class="mt-3 p-3 rounded-lg bg-white border border-emerald-200 text-xs text-gray-700">
                                                    <strong class="text-emerald-800">Instructor Feedback:</strong> {{ $submission->feedback }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Submission Form Accordion / Toggle --}}
                                    <div x-data="{ open: false }">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-semibold text-gray-500">
                                                {{ $isSubmitted ? 'Need to update or re-upload your submission?' : 'Ready to turn in your work?' }}
                                            </span>
                                            <button type="button"
                                                    @click="open = !open"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-[#0A192F] hover:bg-[#14365D] px-4 py-2 text-xs font-bold text-white border border-gold/50 shadow-xs transition cursor-pointer">
                                                <span x-show="!open">{{ $isSubmitted ? 'Re-Submit Output' : 'Pass / Submit Activity' }}</span>
                                                <span x-show="open" x-cloak>Close Submission Form</span>
                                            </button>
                                        </div>

                                        {{-- The Actual Upload Form --}}
                                        <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-line/60">
                                            <form action="{{ route('student.classes.assignments.submit', $assignment) }}"
                                                  method="POST"
                                                  enctype="multipart/form-data"
                                                  class="space-y-4 bg-gray-50 p-5 rounded-xl border border-line/80">
                                                @csrf

                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1.5">
                                                        Attach Output / Activity File (PDF, Word, Zip, Image, Code)
                                                    </label>
                                                    <input type="file"
                                                           name="file"
                                                           class="block w-full text-xs text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0A192F] file:text-[#E7CF9B] hover:file:bg-[#14365D] file:cursor-pointer rounded-lg border border-line/90 bg-white p-2">
                                                    <span class="text-[11px] text-gray-400 mt-1 block">Max file size: 50MB</span>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1.5">
                                                        Submission Link or Student Remarks (Optional)
                                                    </label>
                                                    <textarea name="notes"
                                                              rows="2"
                                                              placeholder="e.g. GitHub Repository link, Google Drive link, or submission remarks..."
                                                              class="w-full rounded-lg border border-line/90 bg-white p-3 text-xs text-navy focus:border-gold/70 focus:ring-1 focus:ring-gold/70 transition">{{ $submission?->notes }}</textarea>
                                                </div>

                                                <div class="flex items-center justify-end gap-3 pt-2">
                                                    <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-600 hover:text-navy hover:bg-gray-200/60 transition cursor-pointer">
                                                        Cancel
                                                    </button>
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] hover:from-[#14365D] hover:to-[#0B213D] hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] px-6 py-2.5 text-xs font-bold text-white border border-gold/40 hover:border-gold hover:shadow-[0_8px_20px_rgba(10,25,47,0.35)] transition-all duration-200 cursor-pointer">
                                                        <span>Submit Output &rarr;</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- ── RIGHT COLUMN: REVIEW FILES & SCHEDULE ───────────────────────── --}}
        <div class="space-y-6">

            {{-- 1. Subject Review Files --}}
            <div class="bg-white rounded-2xl border border-line/80 p-6 shadow-[0_2px_14px_rgba(10,25,47,0.03)]">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-line/60">
                    <div class="flex items-center gap-2">
                        <x-icon name="folder" class="h-5 w-5 text-gold shrink-0" />
                        <h3 class="font-serif text-lg font-bold text-navy">Class & Review Files</h3>
                    </div>
                    <span class="text-xs text-gray-400 font-semibold">{{ $subject->resources->count() }} Files</span>
                </div>

                @if ($subject->resources->isEmpty())
                    <p class="text-xs text-gray-400 italic text-center py-4">No review files uploaded yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($subject->resources as $res)
                            <div class="p-3.5 rounded-xl border border-line/70 bg-[#FAF8F5] hover:bg-white hover:border-gold/50 transition shadow-2xs">
                                <div class="flex items-start gap-3">
                                    <x-icon name="document-text" class="h-5 w-5 text-indigo-500 shrink-0 mt-0.5" />
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-bold text-navy leading-snug">{{ $res->title }}</h4>
                                        @if ($res->description)
                                            <p class="text-[11px] text-gray-500 mt-1 line-clamp-2">{{ $res->description }}</p>
                                        @endif
                                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-line/50">
                                            <span class="text-[10px] text-gray-400 uppercase font-mono">
                                                {{ strtoupper($res->file_type ?? 'PDF') }}
                                                @if ($res->file_size) &bull; {{ number_format($res->file_size / 1024 / 1024, 1) }} MB @endif
                                            </span>
                                            @if ($res->file_path)
                                                <a href="{{ asset('storage/' . $res->file_path) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-navy">
                                                    Download &darr;
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                                                    Available for Review
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 2. Schedule Card --}}
            <div class="bg-white rounded-2xl border border-line/80 p-6 shadow-[0_2px_14px_rgba(10,25,47,0.03)]">
                <h3 class="font-serif text-base font-bold text-navy mb-3 flex items-center gap-2">
                    <x-icon name="calendar" class="h-4 w-4 text-gold" /> Class Schedule
                </h3>
                <div class="space-y-2">
                    @foreach ($subject->schedules as $slot)
                        <div class="rounded-xl bg-gray-50 p-3 border border-line/60 text-xs">
                            <div class="flex items-center justify-between font-bold text-navy mb-1">
                                <span>{{ $slot['day'] }}</span>
                                <span class="rounded px-1.5 py-0.5 text-[10px] {{ $slot['type'] === 'Lec' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $slot['type'] }}
                                </span>
                            </div>
                            <div class="text-gray-500 text-[11px]">
                                {{ $slot['time_start'] }} – {{ $slot['time_end'] }}
                            </div>
                            <div class="text-[11px] font-semibold mt-1 {{ str_contains($slot['room'] ?? '', 'ONLINE') ? 'text-emerald-700' : 'text-gray-600' }}">
                                {{ $slot['room'] ?? 'Campus' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
</x-layouts.app>
