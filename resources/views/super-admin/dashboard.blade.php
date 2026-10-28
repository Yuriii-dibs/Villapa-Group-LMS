<x-layouts.app>
    <x-slot:title>School Administrator Center - Villapa LMS</x-slot:title>

    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900">School Administrator Center</h1>
            <p class="mt-1 text-sm text-gray-500">Welcome, {{ $user->name }}. Institutional control over academic offerings, sections, user administration, and schedules.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-amber-500">
                <h3 class="text-sm font-medium text-gray-500">System Role</h3>
                <p class="mt-2 text-xl font-bold text-amber-800">School Administrator</p>
                <p class="mt-1 text-xs text-gray-400">Institutional management</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-indigo-500">
                <h3 class="text-sm font-medium text-gray-500">Active Roles</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900">3</p>
                <p class="mt-1 text-xs text-gray-400">Student, Instructor, Administrator</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-emerald-500">
                <h3 class="text-sm font-medium text-gray-500">Database Health</h3>
                <p class="mt-2 text-xl font-bold text-emerald-600">Operational</p>
                <p class="mt-1 text-xs text-gray-400">MariaDB / InnoDB utf8mb4</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-blue-500">
                <h3 class="text-sm font-medium text-gray-500">Security Policies</h3>
                <p class="mt-2 text-xl font-bold text-blue-600">Enforced</p>
                <p class="mt-1 text-xs text-gray-400">RBAC + Suspended Guards</p>
            </div>
        </div>
    </div>
</x-layouts.app>
