<footer class="mt-auto bg-navy text-indigo-200">
    <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <x-logo :dark="true" />
        <nav class="flex gap-5">
            <a href="{{ route('programs.index') }}" class="hover:text-paper">Programs</a>
            @guest
                <a href="{{ route('login') }}" class="hover:text-paper">Sign In</a>
                <a href="{{ route('register') }}" class="hover:text-paper">Register</a>
            @else
                <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="hover:text-paper">Dashboard</a>
            @endguest
        </nav>
        <p class="text-xs text-indigo-300">&copy; {{ date('Y') }} UEP - University of Eastern Pangasinan</p>
    </div>
</footer>
