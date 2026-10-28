<x-layouts.guest>
    <x-slot:title>500 Server Error - Villapa LMS</x-slot:title>

    <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-sm border border-gray-200 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-6">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <span class="text-xs font-bold uppercase tracking-wider text-red-600">Error 500</span>
            <h1 class="text-2xl font-black text-gray-900 mt-2">Server Error</h1>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                Something went wrong on our end. Please try again or return to the home screen.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url()->previous() ?: route('home') }}"
                   class="inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                    &larr; Try Again
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
