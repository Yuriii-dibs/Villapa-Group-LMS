<x-layouts.app>
    <x-slot:title>{{ $assignment->title }} – Assignment Details</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Breadcrumb Navigation --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('student.assignments.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-navy transition">
                <span>&larr;</span> Back to All Assignments
            </a>
            <a href="{{ route('student.classes.show', $assignment->subject_id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-navy transition">
                <span>Go to {{ $assignment->subject->subject_code }} Classroom</span>
                <span>&rarr;</span>
            </a>
        </div>

        {{-- Session Flash Alert --}}
        @if (session('status'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <x-icon name="check" class="h-5 w-5 text-emerald-600 shrink-0" />
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-800 shadow-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <p>&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- ── 1. ASSIGNMENT DETAILS HEADER CARD ──────────────────────────────── --}}
        <div class="rounded-2xl border border-line/80 bg-white p-6 sm:p-8 shadow-[0_2px_14px_rgba(10,25,47,0.03)]">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b border-line/60">
                <div>
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="inline-flex items-center rounded-md bg-[#0A192F] px-2.5 py-0.5 font-mono text-xs font-bold text-gold">
                            {{ $assignment->subject->subject_code }}
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ $assignment->subject->title }} &bull; Prof. {{ $assignment->subject->instructor }}
                        </span>
                    </div>
                    <h1 class="font-serif text-2xl sm:text-3xl font-bold text-navy">
                        {{ $assignment->title }}
                    </h1>
                </div>

                <div class="flex items-center sm:flex-col sm:items-end gap-2 shrink-0">
                    <span class="inline-flex items-center rounded-xl bg-gold/15 px-3 py-1 font-mono text-xs font-bold text-gold border border-gold/40">
                        {{ $assignment->total_points }} Points
                    </span>
                    @if ($assignment->due_date)
                        <div class="text-xs sm:text-right font-medium {{ $assignment->due_date->isPast() ? 'text-red-600' : 'text-gray-500' }}">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Due Date</span>
                            {{ $assignment->due_date->format('M d, Y – g:i A') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Instructions / Body --}}
            <div class="pt-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Activity Instructions</h3>
                <div class="prose max-w-none text-gray-700 text-sm leading-relaxed whitespace-pre-line bg-[#FAF8F5] p-5 rounded-xl border border-line/60">
                    {{ $assignment->description ?: 'No detailed instructions provided. Please refer to your lecture discussions or consult your course professor.' }}
                </div>
            </div>
        </div>

        {{-- ── 2. PREVIOUS SUBMISSION & FEEDBACK (IF SUBMITTED) ────────────────── --}}
        @if ($submission)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/20 p-6 sm:p-8 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-emerald-200">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-800">
                            <x-icon name="check" class="h-4 w-4" />
                        </span>
                        <div>
                            <h3 class="font-serif text-lg font-bold text-navy">Your Passed Submission</h3>
                            <p class="text-xs text-gray-500">
                                Submitted on {{ $submission->submitted_at->format('M d, Y – g:i A') }} ({{ $submission->submitted_at->diffForHumans() }})
                            </p>
                        </div>
                    </div>

                    @if ($submission->grade !== null)
                        <div class="rounded-xl bg-emerald-700 px-4 py-2 text-white text-center shadow-xs">
                            <span class="block text-[10px] uppercase tracking-wider font-semibold text-emerald-100">Official Grade</span>
                            <span class="font-mono text-base font-bold">{{ $submission->grade }} / {{ $assignment->total_points }}</span>
                        </div>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 border border-amber-200">
                            <x-icon name="clock" class="h-3.5 w-3.5" /> Awaiting Evaluation
                        </span>
                    @endif
                </div>

                <div class="space-y-4">
                    {{-- Submission Mode Display --}}
                    @if ($submission->submission_type === 'link' && $submission->link_url)
                        <div class="rounded-xl bg-white p-4 border border-emerald-200 flex items-center justify-between">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <x-icon name="compass" class="h-5 w-5 text-indigo-600 shrink-0" />
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-gray-400 block">External Link / Repository</span>
                                    <a href="{{ $submission->link_url }}" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-indigo-700 hover:underline truncate block">
                                        {{ $submission->link_url }}
                                    </a>
                                </div>
                            </div>
                            <a href="{{ $submission->link_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 text-xs font-bold text-indigo-700 transition">
                                <span>Open Link</span>
                                <span>&nearr;</span>
                            </a>
                        </div>
                    @elseif ($submission->submission_type === 'text' && $submission->content_text)
                        <div class="rounded-xl bg-white p-4 border border-emerald-200">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Written Text Content</span>
                            <div class="text-xs text-gray-800 leading-relaxed whitespace-pre-line bg-gray-50 p-3.5 rounded-lg border border-gray-100">
                                {{ $submission->content_text }}
                            </div>
                        </div>
                    @elseif ($submission->file_path)
                        <div class="rounded-xl bg-white p-4 border border-emerald-200 flex items-center justify-between">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <x-icon name="document-text" class="h-5 w-5 text-emerald-600 shrink-0" />
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Submitted File</span>
                                    <span class="text-xs font-bold text-navy truncate block">{{ $submission->file_name ?? 'Assignment File' }}</span>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank"
                               class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-800 transition">
                                <span>Download File</span>
                                <span>&darr;</span>
                            </a>
                        </div>
                    @endif

                    @if ($submission->notes)
                        <div class="text-xs text-gray-600 italic">
                            <strong>Student Remarks:</strong> {{ $submission->notes }}
                        </div>
                    @endif

                    @if ($submission->feedback)
                        <div class="mt-4 p-4 rounded-xl bg-white border border-emerald-300 text-xs">
                            <strong class="text-emerald-900 block mb-1">Professor Feedback:</strong>
                            <p class="text-gray-700 leading-relaxed">{{ $submission->feedback }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── 3. SUBMISSION FORM (TEXT, FILE, OR LINK) ───────────────────────── --}}
        <div class="rounded-2xl border border-line/80 bg-white p-6 sm:p-8 shadow-[0_2px_14px_rgba(10,25,47,0.03)]"
             x-data="{
                submissionType: '{{ $submission->submission_type ?? 'file' }}'
             }">
            <div class="pb-4 mb-6 border-b border-line/60">
                <h2 class="font-serif text-xl font-bold text-navy">
                    {{ $submission ? 'Update or Re-submit Output' : 'Submit Activity Output' }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Choose your submission format: upload a local document/archive, write text directly, or provide an external URL.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('student.classes.assignments.submit', $assignment) }}"
                  enctype="multipart/form-data"
                  class="space-y-6">
                @csrf

                {{-- Mode Selector Tabs (File, Text, Link) --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                        Select Submission Mode
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button"
                                @click="submissionType = 'file'"
                                :class="submissionType === 'file'
                                    ? 'bg-[#0A192F] text-gold border-gold/50 shadow-xs'
                                    : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-200'"
                                class="flex items-center justify-center gap-2 rounded-xl border p-3 text-xs font-bold transition">
                            <x-icon name="document-text" class="h-4 w-4" />
                            <span>File Upload</span>
                        </button>

                        <button type="button"
                                @click="submissionType = 'text'"
                                :class="submissionType === 'text'
                                    ? 'bg-[#0A192F] text-gold border-gold/50 shadow-xs'
                                    : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-200'"
                                class="flex items-center justify-center gap-2 rounded-xl border p-3 text-xs font-bold transition">
                            <x-icon name="book" class="h-4 w-4" />
                            <span>Online Text</span>
                        </button>

                        <button type="button"
                                @click="submissionType = 'link'"
                                :class="submissionType === 'link'
                                    ? 'bg-[#0A192F] text-gold border-gold/50 shadow-xs'
                                    : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-200'"
                                class="flex items-center justify-center gap-2 rounded-xl border p-3 text-xs font-bold transition">
                            <x-icon name="compass" class="h-4 w-4" />
                            <span>Resource Link</span>
                        </button>
                    </div>
                    <input type="hidden" name="submission_type" :value="submissionType">
                </div>

                {{-- Option 1: File Upload --}}
                <div x-show="submissionType === 'file'" x-cloak class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Attach Document / Archive (PDF, DOCX, ZIP, PPTX)</label>
                    <div class="rounded-xl border border-dashed border-gray-300 bg-[#FAF8F5] p-5 text-center">
                        <input type="file" name="file" id="assignment_file"
                               class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0A192F] file:text-gold hover:file:bg-[#14365D] file:transition file:cursor-pointer cursor-pointer">
                        <p class="text-[11px] text-gray-400 mt-2">Maximum file size: 50MB per upload.</p>
                    </div>
                </div>

                {{-- Option 2: Online Text --}}
                <div x-show="submissionType === 'text'" x-cloak class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Written Response / Code Snippet / Essay</label>
                    <textarea name="content_text" rows="8"
                              placeholder="Type or paste your answer, documentation, or code solution here..."
                              class="w-full rounded-xl border border-line bg-[#FAF8F5] p-4 text-xs font-mono focus:border-gold focus:bg-white focus:ring-1 focus:ring-gold transition">{{ old('content_text', $submission->content_text ?? '') }}</textarea>
                </div>

                {{-- Option 3: Resource Link --}}
                <div x-show="submissionType === 'link'" x-cloak class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700">External URL (GitHub, Google Drive, Figma, Docs)</label>
                    <input type="url" name="link_url"
                           value="{{ old('link_url', $submission->link_url ?? '') }}"
                           placeholder="https://github.com/username/project or https://drive.google.com/..."
                           class="w-full rounded-xl border border-line bg-[#FAF8F5] p-3 text-xs focus:border-gold focus:bg-white focus:ring-1 focus:ring-gold transition">
                    <p class="text-[11px] text-gray-400">Make sure link permissions are set to 'Anyone with the link can view' for your professor.</p>
                </div>

                {{-- General Submission Remarks / Notes --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Additional Remarks (Optional)</label>
                    <textarea name="notes" rows="2"
                              placeholder="Any comments, notes, or explanations for your professor..."
                              class="w-full rounded-xl border border-line bg-[#FAF8F5] p-3 text-xs focus:border-gold focus:bg-white focus:ring-1 focus:ring-gold transition">{{ old('notes', $submission->notes ?? '') }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-gray-400">Official digital submission</span>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#071321] via-[#0A192F] to-[#14365D] px-6 py-3 text-xs font-bold text-paper border border-gold/40 hover:border-gold hover:shadow-md transition">
                        <x-icon name="check" class="h-4 w-4 text-gold" />
                        <span>{{ $submission ? 'Re-submit Activity Output' : 'Submit Activity Output' }}</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
