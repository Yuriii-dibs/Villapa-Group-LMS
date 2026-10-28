<header class="sticky top-0 z-40 border-b border-line bg-paper/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}"><x-logo /></a>
        <div class="flex items-center gap-2">
            @auth
                <x-button size="sm" :href="route(auth()->user()->dashboardRoute())">Dashboard</x-button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button type="submit" size="sm" variant="ghost">Log Out</x-button>
                </form>
            @else
                <x-button size="sm" variant="ghost" :href="route('login')">Sign In</x-button>
                <x-button size="sm" :href="route('register')">Register</x-button>
            @endauth
        </div>
    </div>
</header>
