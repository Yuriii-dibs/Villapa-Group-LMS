<x-layouts.guest>
    <x-slot:title>Create Account – UEP LMS</x-slot:title>

    <x-auth-shell heading="Create Student Account" subtitle="Register to get started. An admin will approve your account.">

        {{-- Approval notice banner --}}
        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 mb-5">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <p class="text-xs text-amber-800 leading-relaxed">
                <strong>Admin approval required.</strong> After registering, your account will be reviewed before you can log in.
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            <x-input name="name" label="Full name" required autofocus autocomplete="name" />
            <x-input name="email" type="email" label="Email" required autocomplete="username" />
            <x-password-input autocomplete="new-password" />
            <x-password-input name="password_confirmation" label="Confirm password" autocomplete="new-password" />

            <x-button type="submit" class="w-full">Submit Registration</x-button>
        </form>

        <p class="mt-8 border-t border-line pt-6 text-center text-sm text-gray-500">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-navy">Sign in</a>
        </p>
    </x-auth-shell>
</x-layouts.guest>

