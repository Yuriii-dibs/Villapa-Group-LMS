@php
    $user = auth()->user();
    $role = $user->getPrimaryRole();

    // [label, route name, icon, active pattern]
    $nav = [['Dashboard', $user->dashboardRoute(), 'home', '*.dashboard']];

    if ($user->hasRole('student')) {
        $nav[] = ['My Subjects', 'student.classes.index', 'book', 'student.classes.*'];
        $nav[] = ['Assignments', 'student.assignments.index', 'inbox', 'student.assignments.*'];
    }
    if ($user->hasRole('instructor')) {
        $nav[] = ['My Classes', 'instructor.offerings.index', 'book', 'instructor.offerings.*'];
        $nav[] = ['Assignments', 'instructor.assignments.index', 'inbox', 'instructor.assignments.*'];
    }
    if ($user->hasRole(['admin', 'super-admin'])) {
        $nav[] = ['Users', 'admin.users.index', 'users', 'admin.users.*'];
        $nav[] = ['Enrollment', 'admin.enrollment.index', 'users', 'admin.enrollment.*'];
        $nav[] = ['Offerings', 'admin.offerings.index', 'book', 'admin.offerings.*'];
        $nav[] = ['Subjects', 'admin.subjects.index', 'tag', 'admin.subjects.*'];
        $nav[] = ['Sections', 'admin.sections.index', 'folder', 'admin.sections.*'];
        $nav[] = ['Terms', 'admin.terms.index', 'calendar', 'admin.terms.*'];
        $nav[] = ['Announcements', 'admin.announcements.index', 'chat', 'admin.announcements.*'];
        $nav[] = ['Moderation', 'admin.chat-moderation.index', 'flag', 'admin.chat-moderation.*'];
    }

    if ($user->hasRole(['student', 'instructor'])) {
        $nav[] = ['Schedule', 'schedule.index', 'calendar', 'schedule.*'];
    }

    $nav[] = ['Calendar', 'calendar.index', 'clock', 'calendar.*'];
    $nav[] = ['Messages', 'chat.index', 'chat', 'chat.*'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'UEP LMS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ivory font-sans text-navy antialiased relative">
<x-petals />
<x-notification-bell />
<div x-data="{ open: false }" class="min-h-screen lg:flex relative z-10">

    {{-- Mobile top bar --}}
    <div class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-line bg-paper px-4 lg:hidden">
        <a href="{{ route($user->dashboardRoute()) }}"><x-logo /></a>
        <button type="button" @click="open = true" class="rounded-lg p-2 text-navy hover:bg-ivory" aria-label="Open menu">
            <x-icon name="menu" />
        </button>
    </div>

    {{-- Mobile overlay --}}
    <div x-show="open" x-cloak @click="open = false" x-transition.opacity class="fixed inset-0 z-40 bg-navy/50 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col overflow-hidden bg-[#0A192F] border-r border-gold/20 text-paper transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 shadow-2xl">
        <div class="flex h-16 shrink-0 items-center justify-between px-5 bg-[#071321] border-b border-gold/15">
            <a href="{{ route($user->dashboardRoute()) }}"><x-logo :dark="true" /></a>
            <button type="button" @click="open = false" class="rounded-lg p-1.5 text-indigo-200 hover:text-paper lg:hidden" aria-label="Close menu">
                <x-icon name="close" />
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 bg-[#0A192F]">
            @foreach ($nav as [$label, $routeName, $icon, $pattern])
                @php $active = request()->routeIs($pattern); @endphp
                <a href="{{ route($routeName) }}"
                   class="group flex items-center gap-3 rounded-lg py-2.5 text-sm transition-all duration-200 ease-out hover:translate-x-1
                          {{ $active ? 'border-l-2 border-gold bg-paper/15 text-paper font-semibold shadow-xs pl-2.5' : 'text-indigo-100 hover:bg-paper/10 hover:text-paper pl-3' }}">
                    <x-icon :name="$icon" class="h-[18px] w-[18px] transition-transform duration-200 group-hover:scale-110 {{ $active ? 'text-gold' : 'text-indigo-300' }}" />
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="relative shrink-0 border-t border-gold/15 px-4 pb-24 pt-4 bg-[#071321]">
            <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-lg p-1.5 transition-all duration-150 hover:bg-paper/5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-700 to-navy text-sm font-serif font-medium text-gold border border-gold/60 ring-1 ring-gold/30 shadow-xs transition-transform duration-200 group-hover:scale-105">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm text-paper font-medium">{{ $user->name }}</span>
                    <span class="block text-xs text-gold font-serif">{{ $user->getPrimaryRoleDisplayName() }}</span>
                </span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-indigo-200 hover:bg-paper/10 hover:text-paper transition-all duration-150">
                    <x-icon name="logout" class="h-[18px] w-[18px]" /> Log Out
                </button>
            </form>
            <img src="{{ asset('images/waves/wave-panel.svg') }}" alt="" class="pointer-events-none absolute inset-x-0 bottom-0 h-20 w-full opacity-60">
        </div>
    </aside>

    {{-- Content --}}
    <div class="min-w-0 flex-1">
        <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10 animate-fade-in">
            @if (session('status'))
                <x-alert class="mb-6">{{ session('status') }}</x-alert>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
