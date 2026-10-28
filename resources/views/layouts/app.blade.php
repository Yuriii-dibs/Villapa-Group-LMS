<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'LMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans text-gray-900 antialiased min-h-screen flex flex-col">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <div class="flex items-center space-x-6">
                <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="font-bold text-xl text-indigo-600">
                    Villapa LMS
                </a>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase bg-indigo-100 text-indigo-800">
                    {{ auth()->user()->getPrimaryRole() }}
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-indigo-600">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">Log Out</button>
                </form>
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8 w-full">
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <p class="text-sm text-green-700">{{ session('status') }}</p>
            </div>
        </div>
    @endif

    <main class="flex-1 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </main>
</body>
</html>
