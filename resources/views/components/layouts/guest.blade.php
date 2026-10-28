<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'UEP LMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ivory font-sans text-navy antialiased min-h-screen flex flex-col relative">
    <x-petals />
    <div class="relative z-10 flex min-h-screen flex-col">
        {{ $slot }}
    </div>
</body>
</html>
