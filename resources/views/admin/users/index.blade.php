<x-layouts.app>
    <x-slot:title>User Administration – Villapa LMS</x-slot:title>

    <div class="space-y-6">
        {{-- ── 1. PAGE HEADER & ADD USER TRIGGER ─────────────────────────────── --}}
        <div x-data="{ addOpen: false }">
            <x-page-header
                title="User Administration"
                subtitle="Manage student, faculty, and school administrator accounts, section assignments, and security."
            >
                <x-slot:actions>
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-white px-3.5 py-2 text-xs sm:text-sm font-semibold text-navy hover:bg-[#FAF8F5] transition shadow-2xs">
                        <span>&larr;</span> Dashboard
                    </a>
                    <button type="button" @click="addOpen = true"
                            class="inline-flex items-center gap-2 rounded-xl border border-gold/40 bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] px-4 py-2 text-xs sm:text-sm font-bold text-gold hover:border-gold/70 hover:text-white transition shadow-sm">
                        <x-icon name="plus" class="h-4 w-4 text-gold" />
                        <span>Add New User</span>
                    </button>
                </x-slot:actions>
            </x-page-header>

            {{-- Add User Modal (Teleported to body to avoid container overflow clipping) --}}
            <template x-teleport="body">
                <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div x-show="addOpen" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity" @click="addOpen = false"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                        <div x-show="addOpen" class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle bg-[#FFFEFA] rounded-2xl shadow-2xl border border-line transform transition-all">
                            <form method="POST" action="{{ route('admin.users.store') }}">
                                @csrf
                                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-line/60">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold/15 text-gold border border-gold/40">
                                        <x-icon name="user" class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h3 class="font-serif text-lg font-bold text-navy">Create User Account</h3>
                                        <p class="text-xs text-gray-500">Add a new student, faculty member, or school administrator.</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Account Role *</label>
                                        <select name="role" required class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                            <option value="student">Student</option>
                                            <option value="instructor">Faculty / Instructor</option>
                                            <option value="admin">School Administrator</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Full Name *</label>
                                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Juan Dela Cruz" required
                                               class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Email Address *</label>
                                        <input type="email" name="email" value="{{ old('email') }}" placeholder="user@uep.edu.ph" required
                                               class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Password *</label>
                                            <input type="password" name="password" placeholder="Min 8 chars" required
                                                   class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Confirm Password *</label>
                                            <input type="password" name="password_confirmation" placeholder="Re-enter password" required
                                                   class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Degree Program</label>
                                            <select name="program_id" class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                                <option value="">-- None --</option>
                                                @foreach ($programs as $program)
                                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                                        {{ $program->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Section Cohort</label>
                                            <select name="section_id" class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                                <option value="">-- None --</option>
                                                @foreach ($sections as $section)
                                                    <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                        {{ $section->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 pt-4 border-t border-line/60 flex flex-row-reverse gap-2.5">
                                    <button type="submit" class="inline-flex justify-center rounded-xl bg-[#0A192F] px-5 py-2.5 text-xs font-bold text-gold border border-gold/50 shadow-sm hover:bg-[#14365D] hover:text-white transition">
                                        Create User
                                    </button>
                                    <button type="button" @click="addOpen = false" class="inline-flex justify-center rounded-xl border border-line bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-[#FAF8F5] transition">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- ── 2. FILTER & SEARCH TOOLBAR ────────────────────────────────────── --}}
        <div class="rounded-2xl border border-line/80 bg-[#FFFEFA] p-5 shadow-[0_2px_14px_rgba(10,25,47,0.03)]">
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                <div class="lg:col-span-5">
                    <label for="search" class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Search Directory</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <x-icon name="search" class="h-4 w-4" />
                        </div>
                        <input type="text" id="search" name="search" value="{{ $currentSearch }}" placeholder="Search by name or email address..."
                               class="w-full rounded-xl border border-line bg-white pl-9 pr-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold placeholder:text-gray-400">
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <label for="role" class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Role Filter</label>
                    <select id="role" name="role" class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                        <option value="">All Roles</option>
                        <option value="student" {{ $currentRole === 'student' ? 'selected' : '' }}>Student</option>
                        <option value="instructor" {{ $currentRole === 'instructor' ? 'selected' : '' }}>Faculty / Instructor</option>
                        <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>School Administrator</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                        <option value="">All Statuses</option>
                        <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="suspended" {{ $currentStatus === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 inline-flex justify-center items-center rounded-xl bg-[#0A192F] px-4 py-2 text-xs font-bold text-gold border border-gold/50 shadow-sm hover:bg-[#14365D] hover:text-white transition">
                        Filter
                    </button>
                    @if ($currentSearch || $currentRole || $currentStatus)
                        <a href="{{ route('admin.users.index') }}" title="Clear filters"
                           class="inline-flex justify-center items-center rounded-xl border border-line bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-[#FAF8F5] transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- ── 3. SESSION STATUS / ERROR NOTICES ──────────────────────────────── --}}
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs sm:text-sm font-semibold text-emerald-800 shadow-2xs">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs sm:text-sm font-semibold text-rose-800 shadow-2xs">
                {{ session('error') }}
            </div>
        @endif

        {{-- ── 4. USERS TABLE WITH OPTIMIZED INLINE ACTION BUTTONS ───────────── --}}
        <div class="overflow-hidden rounded-2xl border border-line/80 bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)]">
            @if ($users->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    <x-icon name="user" class="mx-auto h-12 w-12 text-gray-300 mb-2" />
                    <h3 class="font-serif text-base font-bold text-navy">No users found</h3>
                    <p class="text-xs text-gray-500 mt-1">Try adjusting your search criteria or role filters.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm divide-y divide-line/60">
                        <thead class="bg-[#FAF8F5] border-b border-line text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-3.5">User Identity</th>
                                <th class="px-5 py-3.5">Program & Section</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line/50 bg-white">
                            @foreach ($users as $user)
                                @php
                                    $isSelf = ($user->id === auth()->id());
                                    $isStudent = $user->isStudent();
                                    $isInstructor = $user->hasRole('instructor');
                                    $primaryRoleName = $user->getPrimaryRoleDisplayName();
                                @endphp
                                <tr class="hover:bg-[#FAF8F5]/60 transition" x-data="{
                                    editOpen: false,
                                    resetOpen: false,
                                    statusOpen: false,
                                    deleteOpen: false,
                                    approveStudentOpen: false
                                }">

                                    {{-- USER IDENTITY --}}
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0A192F] font-serif font-bold text-xs text-gold border border-gold/40 shadow-2xs">
                                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-bold text-navy truncate">{{ $user->name }}</span>
                                                    @if ($isSelf)
                                                        <span class="rounded bg-gold/15 px-1.5 py-0.5 text-[10px] font-bold text-gold border border-gold/30">(You)</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-400 font-mono truncate">{{ $user->email }}</div>
                                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase
                                                        {{ in_array($user->getPrimaryRole(), ['admin','super-admin']) ? 'bg-amber-100 text-amber-900 border border-amber-200' : ($user->getPrimaryRole() === 'instructor' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-gray-100 text-gray-700 border border-gray-200') }}">
                                                        {{ $primaryRoleName }}
                                                    </span>
                                                    <span class="text-[10px] text-gray-400">&bull; {{ $user->created_at->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- PROGRAM & SECTION --}}
                                    <td class="px-5 py-3.5 text-xs">
                                        @if ($isStudent)
                                            <div class="space-y-1">
                                                <div>
                                                    @if ($user->program)
                                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 border border-indigo-200">
                                                            {{ $user->program->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400 italic">No program</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if ($user->section)
                                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-mono font-medium text-gray-700">
                                                            Section {{ $user->section->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @if ($user->status === 'active')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active
                                            </span>
                                        @elseif ($user->status === 'pending')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span> Pending
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Suspended
                                            </span>
                                        @endif
                                    </td>

                                    {{-- OPTIMIZED ACTION BUTTONS --}}
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        @if ($isSelf)
                                            <span class="text-xs text-gray-400 font-medium italic">Active Account</span>
                                        @else
                                            <div class="inline-flex items-center justify-end gap-1.5 flex-wrap">

                                                {{-- Quick Enroll Student Button --}}
                                                @if ($isStudent)
                                                    <a href="{{ route('admin.enrollment.index', ['student_id' => $user->id]) }}"
                                                       title="Assign Section & Enroll Subjects"
                                                       class="inline-flex items-center gap-1 rounded-lg border border-gold/50 bg-[#0A192F] hover:bg-[#14365D] text-gold px-2.5 py-1 text-xs font-semibold shadow-2xs transition">
                                                        <x-icon name="book" class="h-3.5 w-3.5 text-gold" />
                                                        <span>Enroll</span>
                                                    </a>
                                                @endif

                                                {{-- Pending Approvals --}}
                                                @if ($user->status === 'pending')
                                                    @if ($isStudent)
                                                        <button type="button" @click="approveStudentOpen = true"
                                                                title="Approve Student Account"
                                                                style="background-color:#f59e0b;color:#ffffff;"
                                                                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold shadow-2xs transition">
                                                            <span>Approve</span>
                                                        </button>
                                                    @elseif ($isInstructor)
                                                        <form method="POST" action="{{ route('admin.users.approve-instructor', $user) }}" class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                    title="Approve Faculty Account"
                                                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 text-xs font-bold shadow-2xs transition">
                                                                <span>Approve</span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif

                                                {{-- Edit Details Button --}}
                                                <button type="button" @click="editOpen = true"
                                                        title="Edit details, role, and section"
                                                        class="inline-flex items-center gap-1 rounded-lg border border-line bg-white hover:border-gold hover:bg-[#FAF8F5] text-navy px-2.5 py-1 text-xs font-semibold shadow-2xs transition">
                                                    <x-icon name="edit" class="h-3.5 w-3.5 text-navy" />
                                                    <span>Edit</span>
                                                </button>

                                                {{-- Reset Password Button --}}
                                                <button type="button" @click="resetOpen = true"
                                                        title="Reset Password"
                                                        class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50/70 hover:bg-amber-100 text-amber-900 px-2.5 py-1 text-xs font-semibold shadow-2xs transition">
                                                    <x-icon name="key" class="h-3.5 w-3.5 text-amber-700" />
                                                    <span>Password</span>
                                                </button>

                                                {{-- Suspend / Activate Toggle Button --}}
                                                @if ($user->status === 'active')
                                                    <button type="button" @click="statusOpen = true"
                                                            title="Suspend User Access"
                                                            class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 text-gray-600 px-2.5 py-1 text-xs font-semibold shadow-2xs transition">
                                                        <span>Suspend</span>
                                                    </button>
                                                @elseif ($user->status === 'suspended')
                                                    <button type="button" @click="statusOpen = true"
                                                            title="Activate User Access"
                                                            class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 px-2.5 py-1 text-xs font-semibold shadow-2xs transition">
                                                        <span>Activate</span>
                                                    </button>
                                                @endif

                                                {{-- Delete Button (Enabled for Student & Instructor accounts) --}}
                                                @if (!$isSelf && ($isStudent || $isInstructor))
                                                    <button type="button" @click="deleteOpen = true"
                                                            title="Permanently Delete {{ $isInstructor ? 'Faculty Account' : 'Student' }}"
                                                            class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 px-2.5 py-1 text-xs font-semibold shadow-2xs transition">
                                                        <x-icon name="trash" class="h-3.5 w-3.5 text-rose-600" />
                                                        <span>Delete</span>
                                                    </button>
                                                @endif
                                            </div>

                                            {{-- ── ROW MODALS (Teleported to body for zero table overflow clipping) ── --}}

                                            {{-- 1. Edit User Modal --}}
                                            <template x-teleport="body">
                                                <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                                        <div x-show="editOpen" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity" @click="editOpen = false"></div>
                                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                                        <div x-show="editOpen" class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-[#FFFEFA] rounded-2xl shadow-2xl border border-line transform transition-all">
                                                            <div class="flex items-center gap-3 pb-3 mb-4 border-b border-line/60">
                                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gold/15 text-gold border border-gold/40">
                                                                    <x-icon name="edit" class="h-4.5 w-4.5" />
                                                                </div>
                                                                <div>
                                                                    <h3 class="font-serif text-base font-bold text-navy">Edit User Profile</h3>
                                                                    <p class="text-xs text-gray-500">{{ $user->name }} &bull; {{ $user->email }}</p>
                                                                </div>
                                                            </div>

                                                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                                                                @csrf
                                                                @method('PUT')

                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Full Name</label>
                                                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                                                           class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                                                                </div>

                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Email Address</label>
                                                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                                                           class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                                                                </div>

                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Assigned Role</label>
                                                                    <select name="role" class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                                                        <option value="student" {{ $user->getPrimaryRole() === 'student' ? 'selected' : '' }}>Student</option>
                                                                        <option value="instructor" {{ $user->getPrimaryRole() === 'instructor' ? 'selected' : '' }}>Faculty / Instructor</option>
                                                                        <option value="admin" {{ in_array($user->getPrimaryRole(), ['admin', 'super-admin']) ? 'selected' : '' }}>School Administrator</option>
                                                                    </select>
                                                                </div>

                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Degree Program</label>
                                                                    <select name="program_id" class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                                                        <option value="">-- No Program Assigned --</option>
                                                                        @foreach ($programs as $prog)
                                                                            <option value="{{ $prog->id }}" {{ $user->program_id == $prog->id ? 'selected' : '' }}>
                                                                                {{ $prog->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Section Cohort</label>
                                                                    <select name="section_id" class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                                                        <option value="">-- No Section Assigned --</option>
                                                                        @foreach ($sections as $sec)
                                                                            <option value="{{ $sec->id }}" {{ $user->section_id == $sec->id ? 'selected' : '' }}>
                                                                                {{ $sec->name }} (Year {{ $sec->year_level }})
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="mt-5 pt-3 border-t border-line/60 flex flex-row-reverse gap-2">
                                                                    <button type="submit" class="rounded-xl bg-[#0A192F] px-4 py-2 text-xs font-bold text-gold border border-gold/50 shadow-sm hover:bg-[#14365D] hover:text-white transition">
                                                                        Save Changes
                                                                    </button>
                                                                    <button type="button" @click="editOpen = false" class="rounded-xl border border-line bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-[#FAF8F5] transition">
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- 2. Reset Password Modal --}}
                                            <template x-teleport="body">
                                                <div x-show="resetOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                                        <div x-show="resetOpen" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity" @click="resetOpen = false"></div>
                                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                                        <div x-show="resetOpen" class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-[#FFFEFA] rounded-2xl shadow-2xl border border-line transform transition-all">
                                                            <div class="flex items-center gap-3 pb-3 mb-4 border-b border-line/60">
                                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800 border border-amber-300">
                                                                    <x-icon name="key" class="h-4.5 w-4.5" />
                                                                </div>
                                                                <div>
                                                                    <h3 class="font-serif text-base font-bold text-navy">Reset Password</h3>
                                                                    <p class="text-xs text-gray-500">Set a new password for {{ $user->name }}.</p>
                                                                </div>
                                                            </div>

                                                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="space-y-4">
                                                                @csrf
                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">New Password (min 8 chars) *</label>
                                                                    <input type="password" name="password" required minlength="8" placeholder="Enter new password"
                                                                           class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">Confirm New Password *</label>
                                                                    <input type="password" name="password_confirmation" required minlength="8" placeholder="Confirm new password"
                                                                           class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                                                                </div>

                                                                <div class="mt-5 pt-3 border-t border-line/60 flex flex-row-reverse gap-2">
                                                                    <button type="submit" class="rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700 shadow-sm transition">
                                                                        Reset Password
                                                                    </button>
                                                                    <button type="button" @click="resetOpen = false" class="rounded-xl border border-line bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-[#FAF8F5] transition">
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- 3. Suspend / Activate Modal --}}
                                            <template x-teleport="body">
                                                <div x-show="statusOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                                        <div x-show="statusOpen" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity" @click="statusOpen = false"></div>
                                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                                        <div x-show="statusOpen" class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-[#FFFEFA] rounded-2xl shadow-2xl border border-line transform transition-all">
                                                            <h3 class="font-serif text-base font-bold text-navy mb-2">
                                                                Confirm {{ $user->status === 'active' ? 'Account Suspension' : 'Account Activation' }}
                                                            </h3>
                                                            <p class="text-xs text-gray-600 mb-5 leading-relaxed">
                                                                @if ($user->status === 'active')
                                                                    Are you sure you want to suspend <strong>{{ $user->name }}</strong>? They will be locked out of the university system immediately.
                                                                @else
                                                                    Are you sure you want to activate <strong>{{ $user->name }}</strong>? They will regain full access immediately.
                                                                @endif
                                                            </p>
                                                            <div class="flex flex-row-reverse gap-2">
                                                                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="inline">
                                                                    @csrf
                                                                    <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'suspended' : 'active' }}">
                                                                    <button type="submit"
                                                                            class="rounded-xl px-4 py-2 {{ $user->status === 'active' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-xs font-bold text-white shadow-sm transition">
                                                                        Yes, {{ $user->status === 'active' ? 'Suspend Account' : 'Activate Account' }}
                                                                    </button>
                                                                </form>
                                                                <button type="button" @click="statusOpen = false" class="rounded-xl border border-line bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-[#FAF8F5] transition">
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- 4. Delete User Modal (Permanent for Students & Instructors) --}}
                                            @if (!$isSelf && ($isStudent || $isInstructor))
                                                <template x-teleport="body">
                                                    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                                            <div x-show="deleteOpen" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity" @click="deleteOpen = false"></div>
                                                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                                            <div x-show="deleteOpen" class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-[#FFFEFA] rounded-2xl shadow-2xl border border-rose-300 transform transition-all">
                                                                <div class="flex items-center gap-3 pb-3 mb-4 border-b border-rose-100">
                                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700 border border-rose-300">
                                                                        <x-icon name="trash" class="h-5 w-5" />
                                                                    </div>
                                                                    <div>
                                                                        <h3 class="font-serif text-base font-bold text-rose-950">Delete {{ $isInstructor ? 'Faculty' : 'Student' }} Account</h3>
                                                                        <p class="text-xs text-rose-700">Permanent and irreversible action.</p>
                                                                    </div>
                                                                </div>

                                                                <p class="text-xs text-gray-600 mb-4 leading-relaxed">
                                                                    Are you sure you want to permanently delete the {{ $isInstructor ? 'faculty' : 'student' }} account for <strong>{{ $user->name }}</strong> (<span class="font-mono text-gray-500">{{ $user->email }}</span>)?
                                                                </p>

                                                                <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 mb-5 text-[11px] text-rose-800 space-y-1">
                                                                    <p class="font-bold">This will permanently delete:</p>
                                                                    <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                                                                        @if ($isInstructor)
                                                                            <li>Faculty credentials & system account access</li>
                                                                            <li>Direct messages & sent notifications</li>
                                                                            <li>Unlinks assigned offerings (retaining teacher name on schedules)</li>
                                                                        @else
                                                                            <li>All enrolled subject offerings & grades</li>
                                                                            <li>All assignment submissions & uploaded files</li>
                                                                            <li>Discussion & chat messages</li>
                                                                            <li>System login credentials</li>
                                                                        @endif
                                                                    </ul>
                                                                </div>

                                                                <div class="flex flex-row-reverse gap-2">
                                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                                class="rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition">
                                                                            Yes, Delete {{ $isInstructor ? 'Faculty' : 'Student' }}
                                                                        </button>
                                                                    </form>
                                                                    <button type="button" @click="deleteOpen = false" class="rounded-xl border border-line bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-[#FAF8F5] transition">
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            @endif

                                            {{-- 5. Approve Student Modal --}}
                                            @if ($isStudent && $user->status === 'pending')
                                                <template x-teleport="body">
                                                    <div x-show="approveStudentOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                                            <div x-show="approveStudentOpen" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity" @click="approveStudentOpen = false"></div>
                                                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                                            <div x-show="approveStudentOpen" class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-[#FFFEFA] rounded-2xl shadow-2xl border border-line transform transition-all">
                                                                <h3 class="font-serif text-base font-bold text-navy mb-1">Approve Student Registration</h3>
                                                                <p class="text-xs text-gray-500 mb-4">{{ $user->name }} &bull; {{ $user->email }}</p>
                                                                <form method="POST" action="{{ route('admin.users.approve-student', $user) }}" class="space-y-4">
                                                                    @csrf
                                                                    <div>
                                                                        <label class="block text-xs font-bold uppercase tracking-wider text-navy mb-1">
                                                                            Assign Degree Program <span class="text-gray-400 font-normal">(optional)</span>
                                                                        </label>
                                                                        <select name="program_id" class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                                                                            <option value="">-- No Program Assigned --</option>
                                                                            @foreach ($programs as $prog)
                                                                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="flex flex-row-reverse gap-2 pt-2">
                                                                        <button type="submit" style="background-color:#f59e0b;color:#ffffff;" class="rounded-xl px-4 py-2 text-xs font-bold shadow-sm transition">
                                                                            Approve & Activate
                                                                        </button>
                                                                        <button type="button" @click="approveStudentOpen = false" class="rounded-xl border border-line bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-[#FAF8F5] transition">
                                                                            Cancel
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 bg-[#FAF8F5] border-t border-line/60">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
