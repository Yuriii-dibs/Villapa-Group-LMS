<x-layouts.app>
    <x-slot:title>My Coursework & Assignments – UEP LMS</x-slot:title>

    <x-page-header
        title="Assignments & Activities"
        subtitle="Track coursework deadlines, submit laboratory outputs (file, text, or link), and view grades."
    >
        <x-slot:actions>
            <a href="{{ route('student.classes.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-navy hover:bg-gray-50 transition shadow-2xs">
                <x-icon name="book" class="h-4 w-4" /> My Enrolled Subjects
            </a>
        </x-slot:actions>
    </x-page-header>

    <div x-data="{
        filter: 'all',
        match(status) {
            if (this.filter === 'all') return true;
            return this.filter === status;
        }
    }" class="space-y-6">

        {{-- Filter Pills --}}
        <div class="flex flex-wrap items-center gap-2 pb-2 border-b border-line/70">
            <button type="button" @click="filter = 'all'"
                    :class="filter === 'all' ? 'bg-[#0A192F] text-gold border-gold/50 shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border-gray-200'"
                    class="rounded-xl border px-3.5 py-1.5 text-xs font-bold transition">
                All ({{ $assignments->count() }})
            </button>
            <button type="button" @click="filter = 'pending'"
                    :class="filter === 'pending' ? 'bg-[#0A192F] text-gold border-gold/50 shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border-gray-200'"
                    class="rounded-xl border px-3.5 py-1.5 text-xs font-bold transition">
                Pending Submissions
            </button>
            <button type="button" @click="filter = 'submitted'"
                    :class="filter === 'submitted' ? 'bg-[#0A192F] text-gold border-gold/50 shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border-gray-200'"
                    class="rounded-xl border px-3.5 py-1.5 text-xs font-bold transition">
                Passed / Submitted
            </button>
        </div>

        @if ($assignments->isEmpty())
            <x-card>
                <x-empty-state
                    title="No assignments posted"
                    text="Your professors haven't posted any activities or assignments for your enrolled subjects yet."
                />
            </x-card>
        @else
            <div class="space-y-4">
                @foreach ($assignments as $assignment)
                    @php
                        $submission = $assignment->submissions->first();
                        $isSubmitted = $submission !== null;
                        $statusCode = $isSubmitted ? 'submitted' : 'pending';
                    @endphp

                    <div x-show="match('{{ $statusCode }}')"
                         class="rounded-2xl border {{ $isSubmitted ? 'border-emerald-200 bg-white' : 'border-line/80 bg-white' }} p-6 shadow-sm transition hover:border-gold/50">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-1.5 min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center rounded-md bg-[#0A192F] px-2.5 py-0.5 font-mono text-xs font-bold text-gold">
                                        {{ $assignment->subject->subject_code }}
                                    </span>
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
                                        {{ $assignment->total_points }} Points
                                    </span>
                                </div>

                                <h3 class="font-serif text-lg font-bold text-navy">
                                    <a href="{{ route('student.assignments.show', $assignment) }}" class="hover:text-indigo-600 transition">
                                        {{ $assignment->title }}
                                    </a>
                                </h3>

                                <p class="text-xs text-gray-500">
                                    Subject: <span class="font-semibold text-gray-700">{{ $assignment->subject->title }}</span>
                                    &bull; Professor: {{ $assignment->subject->instructor }}
                                </p>

                                @if ($assignment->due_date)
                                    <div class="text-xs font-medium {{ $assignment->due_date->isPast() ? 'text-red-600' : 'text-gray-600' }}">
                                        Deadline: {{ $assignment->due_date->format('M d, Y – g:i A') }} ({{ $assignment->due_date->diffForHumans() }})
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 self-start md:self-center shrink-0">
                                @if ($isSubmitted && $submission->grade !== null)
                                    <span class="inline-flex items-center rounded-xl bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white shadow-xs">
                                        Grade: {{ $submission->grade }} / {{ $assignment->total_points }}
                                    </span>
                                @endif

                                <a href="{{ route('student.assignments.show', $assignment) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-gray-50 hover:bg-[#0A192F] hover:text-gold px-4 py-2 text-xs font-bold text-navy border border-line transition shadow-2xs">
                                    <span>{{ $isSubmitted ? 'View Submission & Feedback' : 'Submit Activity' }}</span>
                                    <span class="text-xs">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-layouts.app>
