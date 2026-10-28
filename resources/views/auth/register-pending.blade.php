<x-layouts.guest>
    <x-slot:title>Account Pending Approval – UEP LMS</x-slot:title>

    <x-auth-shell heading="Registration Submitted!" subtitle="Your account is now under review.">

        <div class="flex flex-col items-center gap-5 py-4">

            {{-- Animated clock / pending icon --}}
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-amber-100 text-amber-500">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                </svg>
            </div>

            <div class="text-center space-y-2">
                <p class="text-sm text-gray-600 leading-relaxed">
                    Your student account has been created and is currently
                    <strong class="text-amber-600">pending admin approval</strong>.
                </p>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Once an administrator reviews and approves your account, you'll be able to
                    log in and start accessing your courses.
                </p>
            </div>

            {{-- Stepper --}}
            <ol class="flex w-full max-w-xs flex-col gap-3 rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm">
                <li class="flex items-center gap-3">
                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white text-xs font-bold">
                        <x-icon name="check" class="h-3.5 w-3.5" />
                    </span>
                    <span class="text-gray-700 font-medium">Account registered</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-amber-400 text-white text-xs font-bold animate-pulse">2</span>
                    <span class="text-amber-700 font-semibold">Waiting for admin approval</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-gray-400 text-xs font-bold">3</span>
                    <span class="text-gray-400">Log in &amp; start learning</span>
                </li>
            </ol>

            <p class="text-xs text-gray-400 text-center">
                Already approved? Try logging in below.
            </p>

            <a href="{{ route('login') }}"
               class="inline-flex w-full max-w-xs items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-navy shadow-sm hover:bg-gray-50 transition">
                ← Back to Login
            </a>
        </div>

    </x-auth-shell>
</x-layouts.guest>
