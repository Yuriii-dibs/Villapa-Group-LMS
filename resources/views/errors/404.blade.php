<x-layouts.guest>
    <x-slot:title>404 Not Found - Villapa LMS</x-slot:title>

    <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-sm border border-gray-200 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 text-indigo-600 mb-6">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Error 404</span>
            <h1 class="text-2xl font-black text-gray-900 mt-2">Page Not Found</h1>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                The page, course, or lesson you are looking for doesn't exist or may have been unpublished.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('courses.index') }}"
                   class="inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                    Browse Courses
                </a>
                <a href="{{ auth()->check() ? route(auth()->user()->dashboardRoute()) : route('home') }}"
                   class="inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    {{ auth()->check() ? 'My Dashboard' : 'Homepage' }}
                </a>
            </div>
        </div>

        <p class="mt-6 text-xs text-gray-400">&copy; {{ date('Y') }} Villapa LMS</p>
    </div>
</x-layouts.guest>
