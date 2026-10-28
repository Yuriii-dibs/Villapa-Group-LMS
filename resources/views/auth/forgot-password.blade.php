<x-layouts.guest>
    <x-slot:title>Forgot Password - Villapa LMS</x-slot:title>

    <x-auth-shell heading="Forgot Your Password?" subtitle="Password resets are handled by the School Administrator.">

        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-5 text-center space-y-2">
            <p class="text-sm font-semibold text-amber-800">
                Password resets are not available online.
            </p>
            <p class="text-xs text-amber-700 leading-relaxed">
                Please visit the School Administrator's office in person with a valid school ID to have your password reset.
            </p>
        </div>

        <div class="mt-5 rounded-xl border border-line bg-[#FFFEFA] px-5 py-4 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-widest text-navy">Admin Office</h3>
            <p class="text-sm text-gray-600 leading-relaxed">
                Bring your school ID and approach the School Administrator's office. The administrator will verify your identity and reset your account credentials for you.
            </p>
        </div>

        <p class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-navy">&larr; Back to sign in</a>
        </p>
    </x-auth-shell>
</x-layouts.guest>
