<x-layouts.app>
    <x-slot:title>Submissions: {{ $assignment->title }} – Faculty Portal</x-slot:title>

    <div class="space-y-8">
        {{-- Header Banner --}}
        <div class="rounded-2xl bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] p-6 text-paper shadow-md border border-gold/30">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center rounded-lg bg-gold/20 px-2.5 py-0.5 font-mono text-xs font-bold text-gold border border-gold/40">
                            {{ $assignment->subject?->subject_code }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-white/10 px-2.5 py-0.5 text-xs font-semibold text-gray-200">
                            Max: {{ $assignment->total_points }} Points
                        </span>
                        @if ($assignment->due_date)
                            <span class="inline-flex items-center rounded-lg bg-white/10 px-2.5 py-0.5 text-xs font-semibold text-gray-200">
                                Due: {{ $assignment->due_date->format('M d, Y h:i A') }}
                            </span>
                        @endif
                    </div>
                    <h1 class="font-serif text-xl sm:text-2xl font-bold text-white tracking-tight">
                        {{ $assignment->title }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('instructor.assignments.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-white/20 bg-white/10 px-3.5 py-2 text-xs font-semibold text-white hover:bg-white/20 transition">
                        <span>&larr; Back to Assignments</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Submissions List --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0A192F] text-gold border border-gold/30">
                        <x-icon name="inbox" class="h-4 w-4" />
                    </span>
                    <h2 class="font-serif text-lg font-bold text-navy tracking-tight">
                        Student Submissions ({{ $assignment->submissions->count() }})
                    </h2>
                </div>
            </div>

            @if ($assignment->submissions->isEmpty())
                <div class="p-12 text-center text-gray-500 bg-[#FFFEFA] rounded-2xl border border-dashed border-line shadow-2xs">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 mb-3">
                        <x-icon name="inbox" class="h-6 w-6" />
                    </div>
                    <p class="text-base font-bold text-navy">No submissions turned in yet</p>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                        Once enrolled learners submit their files, text responses, or links, they will be listed here for review and grading.
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($assignment->submissions as $submission)
                        <div class="rounded-2xl border border-line bg-[#FFFEFA] p-5 shadow-2xs space-y-4 hover:border-gold/50 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-line/60">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-serif font-bold text-navy text-sm sm:text-base">
                                            {{ $submission->user->name }}
                                        </h3>
                                        <span class="text-xs text-gray-400 font-mono">({{ $submission->user->email }})</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                                        <span>Submitted: {{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y h:i A') : 'N/A' }}</span>
                                        @if ($assignment->due_date && $submission->submitted_at && $submission->submitted_at->gt($assignment->due_date))
                                            <span class="rounded bg-rose-50 text-rose-700 border border-rose-200 px-1.5 py-0.2 font-bold text-[10px]">
                                                Late Submission
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold
                                        {{ $submission->grade !== null ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                        {{ $submission->grade !== null ? "Graded: {$submission->grade} / {$assignment->total_points}" : 'Awaiting Grade' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Submission Content by Mode --}}
                            <div class="p-4 rounded-xl bg-gray-50 border border-line/60 space-y-2 text-xs">
                                <span class="block font-bold text-navy uppercase text-[10px] tracking-wider text-gray-400">
                                    Submission Mode: {{ strtoupper($submission->submission_type ?? 'FILE') }}
                                </span>

                                @if ($submission->submission_type === 'link')
                                    <div class="flex items-center gap-2">
                                        <x-icon name="tag" class="h-4 w-4 text-indigo-600 shrink-0" />
                                        <a href="{{ $submission->link_url }}" target="_blank" rel="noopener noreferrer"
                                           class="font-mono font-bold text-indigo-700 hover:underline break-all">
                                            {{ $submission->link_url }} &rarr;
                                        </a>
                                    </div>
                                @elseif ($submission->submission_type === 'text')
                                    <div class="p-3 bg-white rounded-lg border border-line text-gray-700 leading-relaxed whitespace-pre-wrap">
                                        {{ $submission->content_text }}
                                    </div>
                                @else
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <x-icon name="document" class="h-4 w-4 text-gold shrink-0" />
                                            <span class="font-medium text-navy">{{ $submission->file_name ?? 'Attachment File' }}</span>
                                        </div>
                                        @if ($submission->file_path)
                                            <a href="{{ asset('storage/' . $submission->file_path) }}" download
                                               class="inline-flex items-center gap-1 font-bold text-indigo-700 hover:text-navy">
                                                <span>Download File</span>
                                                <span class="text-[10px]">&darr;</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                @if ($submission->notes)
                                    <p class="text-[11px] text-gray-500 italic mt-1 pt-1 border-t border-line/40">
                                        Student Note: {{ $submission->notes }}
                                    </p>
                                @endif
                            </div>

                            {{-- Grading & Feedback Form --}}
                            <form method="POST" action="{{ route('instructor.assignments.grade', $submission) }}"
                                  class="pt-3 border-t border-line/60 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                @csrf

                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                        Score (Max: {{ $assignment->total_points }}) *
                                    </label>
                                    <input type="number" step="0.5" name="grade" min="0" max="{{ $assignment->total_points }}"
                                           value="{{ old('grade', $submission->grade) }}" required
                                           class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                                </div>

                                <div class="sm:col-span-7">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Faculty Feedback & Remarks</label>
                                    <input type="text" name="feedback" value="{{ old('feedback', $submission->feedback) }}"
                                           placeholder="Provide feedback or notes to the student..."
                                           class="w-full text-xs rounded-xl border border-line bg-white px-3 py-2 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                                </div>

                                <div class="sm:col-span-2">
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-[#0A192F] hover:bg-[#14365D] text-gold text-xs font-bold border border-gold/40 transition shadow-2xs">
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                        <span>Save Grade</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
