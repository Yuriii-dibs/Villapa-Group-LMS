<x-layouts.guest>
    <x-slot:title>Reset Password - Villapa LMS</x-slot:title>

    <x-auth-shell heading="Choose a new password">
        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <x-input name="email" type="email" label="Email" :value="$request->email" required autofocus autocomplete="username" />
            <x-password-input label="New password" autocomplete="new-password" />
            <x-password-input name="password_confirmation" label="Confirm password" autocomplete="new-password" />
            <x-button type="submit" class="w-full">Reset Password</x-button>
        </form>

        <p class="mt-8 text-center"><a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-navy">&larr; Back to sign in</a></p>
    </x-auth-shell>
</x-layouts.guest>
