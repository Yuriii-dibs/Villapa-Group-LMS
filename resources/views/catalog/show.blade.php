<x-layouts.guest>
    <x-slot:title>{{ $course->title }} - UEP LMS</x-slot:title>

    <x-public-header />

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
        @if (session('status'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-r-lg text-sm shadow-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r-lg text-sm shadow-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="mb-6">
            <a href="{{ route('courses.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                &larr; Back to Subject Catalog
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Course Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    <div class="flex flex-wrap items-center gap-2 text-xs mb-4">
                        <span class="px-3 py-1 rounded-full font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $course->category->name }}
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mb-6">{{ $course->title }}</h1>

                    <div class="prose max-w-none text-gray-700 text-base leading-relaxed whitespace-pre-line mb-8">
                        {{ $course->description }}
                    </div>

                    @if (!empty($course->learning_objectives))
                        <div class="border-t border-gray-100 pt-6 mt-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">What You Will Learn</h2>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-600">
                                @foreach ($course->learning_objectives as $objective)
                                    <li class="flex items-start bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <x-icon name="check" class="h-4 w-4 text-emerald-600 mr-2.5 shrink-0 mt-0.5" />
                                        <span>{{ $objective }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar / Enrollment Card -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-24">
                    @if ($course->thumbnail_path)
                        <img src="{{ asset('storage/' . $course->thumbnail_path) }}" alt="{{ $course->title }}" class="w-full h-48 object-cover rounded-xl mb-6 shadow-sm">
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-xl mb-6 flex items-center justify-center text-white font-bold text-center p-6 shadow-sm">
                            {{ $course->title }}
                        </div>
                    @endif

                    @if ($isEnrolled)
                        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Your Enrolled Program</span>
                            </div>
                            <p class="mt-1 text-xs text-emerald-700">You are officially registered in this degree program.</p>
                        </div>
                        <a href="{{ route('student.courses.show', $course) }}"
                           class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 text-center transition">
                            Go to Classroom &rarr;
                        </a>
                    @else
                        @auth
                            <div class="rounded-xl border border-line/80 bg-gray-50/80 p-4 text-left">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span class="text-xs font-bold uppercase tracking-wider text-navy">Admin-Assigned Program</span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    Students cannot add, enroll in, or change degree programs. Program assignments are managed exclusively by the school administration.
                                </p>
                                @if (auth()->user()->program)
                                    <div class="mt-3 pt-3 border-t border-line/70">
                                        <span class="text-[10px] uppercase tracking-wider text-gray-400 block font-semibold">Your Official Program</span>
                                        <span class="text-xs font-bold text-navy mt-0.5 block">{{ auth()->user()->program->name }}</span>
                                    </div>
                                @else
                                    <div class="mt-3 pt-3 border-t border-line/70">
                                        <span class="text-xs text-amber-700 italic">No program assigned yet. Contact your administrator.</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mb-5">
                                <span class="text-xl font-black text-navy font-serif">Official Program</span>
                                <p class="text-xs text-gray-500 mt-1">Institutional degree program curriculum of the University of Eastern Pangasinan.</p>
                            </div>
                            <a href="{{ route('login') }}"
                               class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-[#0A192F] hover:bg-[#14365D] text-center transition">
                                Sign In to Access Portal
                            </a>
                            <p class="text-xs text-center text-gray-500 mt-3">
                                Program assignments are officially managed by school administration.
                            </p>
                        @endauth
                    @endif

                    <div class="border-t border-gray-100 mt-6 pt-5 text-xs text-gray-600 space-y-2.5">
                        <p class="flex items-center"><x-icon name="check" class="h-4 w-4 text-emerald-600 mr-2 shrink-0" /> Complete self-paced learning schedule</p>
                        <p class="flex items-center"><x-icon name="check" class="h-4 w-4 text-emerald-600 mr-2 shrink-0" /> Progress tracking on completed lessons</p>
                        <p class="flex items-center"><x-icon name="check" class="h-4 w-4 text-emerald-600 mr-2 shrink-0" /> Live chat &amp; online classroom sessions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-public-footer />
</x-layouts.guest>
