<x-layouts.app>
    <x-slot:title>{{ $lesson->title }} - {{ $course->title }}</x-slot:title>

    @php
        $allLessons = $course->modules->flatMap->lessons->sortBy('order')->values();
        $currentIndex = $allLessons->search(fn($l) => $l->id === $lesson->id);
        $prevLesson = ($currentIndex !== false && $currentIndex > 0) ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = ($currentIndex !== false && $currentIndex < $allLessons->count() - 1) ? $allLessons[$currentIndex + 1] : null;
        $lessonNumber = ($currentIndex !== false) ? $currentIndex + 1 : 1;
        $totalLessons = $allLessons->count();
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">
        @if (session('status'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg text-sm shadow-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Top Navigation Bar & Course Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('student.courses.show', $course) }}"
                       class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                        &larr; Back to Syllabus
                    </a>
                    <span class="text-gray-300">|</span>
                    <span class="text-xs text-gray-500 font-medium truncate max-w-xs sm:max-w-md">
                        {{ $course->title }}
                    </span>
                </div>
                <div class="flex items-center space-x-2 text-xs font-semibold text-gray-600">
                    <span>Lesson {{ $lessonNumber }} of {{ $totalLessons }}</span>
                </div>
            </div>

            <!-- Course Progress Bar -->
            <div>
                <div class="flex justify-between items-center text-xs font-bold text-gray-700 mb-1.5">
                    <span>Course Completion</span>
                    <span class="text-indigo-600">{{ $progressPercentage }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                </div>
            </div>
        </div>

        <!-- Main Lesson Content Card -->
        <article class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8 space-y-6">
            <header class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-gray-100 gap-4">
                <div>
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 uppercase tracking-wide">
                            {{ $lesson->content_type }}
                        </span>
                        @if ($lesson->duration_minutes)
                            <span class="text-xs text-gray-400 font-medium">
                                ~{{ $lesson->duration_minutes }} mins
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">{{ $lesson->title }}</h1>
                    <p class="text-xs text-gray-500 mt-1">Module: <span class="font-semibold text-gray-700">{{ $lesson->module->title }}</span></p>
                </div>

                <!-- Mark as Complete Action Button -->
                <div>
                    <form method="POST" action="{{ route('student.lessons.complete', $lesson) }}">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-bold shadow-sm transition {{ $isCompleted ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-indigo-600 text-white hover:bg-indigo-700' }}">
                            @if ($isCompleted)
                                <x-icon name="check" class="h-4 w-4 mr-1.5 inline" /> Completed
                            @else
                                Mark as Complete
                            @endif
                        </button>
                    </form>
                </div>
            </header>

            <!-- Video Content Embed -->
            @if ($lesson->content_type === 'video' && $lesson->video_url)
                <div class="rounded-xl overflow-hidden bg-black shadow-inner my-4">
                    <div class="relative pb-[56.25%] h-0">
                        <iframe src="{{ $lesson->video_url }}"
                                class="absolute top-0 left-0 w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
            @endif

            <!-- Main Lesson Body Text -->
            @if ($lesson->body)
                <div class="prose max-w-none text-gray-800 text-base leading-relaxed whitespace-pre-line py-2">
                    {{ $lesson->body }}
                </div>
            @endif

            <!-- External Resource / File Link -->
            @if ($lesson->external_url)
                <div class="p-5 bg-indigo-50/60 rounded-xl border border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-indigo-900">External Learning Resource</p>
                        <p class="text-xs text-indigo-700 mt-0.5 break-all">{{ $lesson->external_url }}</p>
                    </div>
                    <a href="{{ $lesson->external_url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 shadow-sm transition whitespace-nowrap">
                        Open Resource &rarr;
                    </a>
                </div>
            @endif

            <!-- Bottom Navigation with Previous, Mark as Complete, and Next Buttons -->
            <footer class="pt-8 mt-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    @if ($prevLesson)
                        <a href="{{ route('student.lessons.show', [$course, $prevLesson]) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                            &larr; Previous Lesson
                        </a>
                    @else
                        <span class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-300 bg-gray-50 cursor-not-allowed">
                            &larr; Previous
                        </span>
                    @endif
                </div>

                <div>
                    <form method="POST" action="{{ route('student.lessons.complete', $lesson) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border rounded-lg text-sm font-bold transition {{ $isCompleted ? 'border-emerald-500 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' : 'border-transparent text-white bg-indigo-600 hover:bg-indigo-700' }}">
                                @if ($isCompleted)
                                    <x-icon name="check" class="h-4 w-4 mr-1.5 inline" /> Completed
                                @else
                                    Mark as Complete
                                @endif
                        </button>
                    </form>
                </div>

                <div>
                    @if ($nextLesson)
                        <a href="{{ route('student.lessons.show', [$course, $nextLesson]) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                            Next Lesson &rarr;
                        </a>
                    @else
                        <a href="{{ route('student.courses.show', $course) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">
                            Complete Syllabus &rarr;
                        </a>
                    @endif
                </div>
            </footer>
        </article>
    </div>
</x-layouts.app>
