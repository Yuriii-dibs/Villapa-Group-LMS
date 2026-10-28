<x-layouts.guest>
    <x-slot:title>Verify Email - Villapa LMS</x-slot:title>

    <x-auth-shell heading="Verify your email" subtitle="Click the link we emailed you. Didn't get it? We can send another.">
        @if (session('status') == 'verification-link-sent')
            <x-alert class="mb-6">A new verification link has been sent to your email address.</x-alert>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-button type="submit">Resend Email</x-button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button type="submit" variant="ghost">Log Out</x-button>
            </form>
        </div>
    </x-auth-shell>
</x-layouts.guest>
