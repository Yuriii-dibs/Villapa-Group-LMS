<x-layouts.app>
    <x-slot:title>Profile - Villapa LMS</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Your Profile</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your account credentials and personal information.</p>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg text-sm shadow-sm">
                Your profile information has been saved successfully.
            </div>
        @elseif (session('status') === 'password-updated')
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg text-sm shadow-sm">
                Your password has been updated successfully.
            </div>
        @endif

        <!-- Profile Details -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 sm:p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Profile Details</h2>
            <p class="text-sm text-gray-500 mb-6">Update your account display name and primary email address.</p>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name') <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email') <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Assigned Roles</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($user->roles as $role)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $role->display_name }}
                            </span>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Role permissions are managed by system administrators.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Degree Program</label>
                    <div class="mt-2">
                        @if ($user->program)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#0A192F] text-gold border border-gold/40">
                                {{ $user->program->name }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400 italic">No program assigned yet. Please contact your school administrator or registrar.</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Degree programs are official and assigned exclusively by the registrar / administration. Students cannot add or change their program.</p>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 shadow-sm transition">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 sm:p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Change Password</h2>
            <p class="text-sm text-gray-500 mb-6">Ensure your account is protected with a strong, distinct password.</p>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div x-data="{ show: false }">
                    <label for="current_password" class="block text-sm font-semibold text-gray-700">Current Password</label>
                    <div class="mt-1.5 relative rounded-lg shadow-sm">
                        <input :type="show ? 'text' : 'password'" id="current_password" name="current_password" required
                               placeholder="••••••••"
                               class="block w-full rounded-lg border-gray-300 shadow-sm border p-2.5 pr-10 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                                aria-label="Toggle password visibility">
                            <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('current_password') <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div x-data="{ show: false }">
                    <label for="new_password" class="block text-sm font-semibold text-gray-700">New Password</label>
                    <div class="mt-1.5 relative rounded-lg shadow-sm">
                        <input :type="show ? 'text' : 'password'" id="new_password" name="password" required
                               placeholder="Minimum 8 characters"
                               class="block w-full rounded-lg border-gray-300 shadow-sm border p-2.5 pr-10 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                                aria-label="Toggle password visibility">
                            <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('password') <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div x-data="{ show: false }">
                    <label for="new_password_confirmation" class="block text-sm font-semibold text-gray-700">Confirm New Password</label>
                    <div class="mt-1.5 relative rounded-lg shadow-sm">
                        <input :type="show ? 'text' : 'password'" id="new_password_confirmation" name="password_confirmation" required
                               placeholder="Repeat new password"
                               class="block w-full rounded-lg border-gray-300 shadow-sm border p-2.5 pr-10 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                                aria-label="Toggle password confirmation visibility">
                            <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 shadow-sm transition">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
