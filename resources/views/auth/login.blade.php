<x-layouts.guest>
    <x-slot:title>Sign In - Villapa LMS</x-slot:title>

    <x-auth-shell heading="Sign in to your account" subtitle="Sign in to continue learning.">
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <x-input name="email" type="email" label="Email" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-password-input />

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-line text-indigo-600 focus:ring-indigo-600">
                    Remember me
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-navy">Forgot password?</a>
                @endif
            </div>

            <x-button type="submit" class="w-full">Sign In</x-button>
        </form>

<!-- Removed create student account link -->
        <p class="mt-4 text-center"><a href="{{ route('home') }}" class="text-xs text-gray-400 hover:text-navy">&larr; Back to catalog</a></p>
    </x-auth-shell>
</x-layouts.guest>
