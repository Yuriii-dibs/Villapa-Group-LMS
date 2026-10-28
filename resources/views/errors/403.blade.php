<x-layouts.guest>
    <x-slot:title>403 Forbidden - Villapa LMS</x-slot:title>

    <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-sm border border-gray-200 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-600 mb-6">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-11a4 4 0 00-8 0v4h8V4z" />
                </svg>
            </div>

            <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Error 403</span>
            <h1 class="text-2xl font-black text-gray-900 mt-2">Access Denied</h1>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                {{ $exception->getMessage() ?: 'You do not have permission to access this resource or classroom.' }}
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url()->previous() ?: route('home') }}"
                   class="inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                    &larr; Go Back
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
