<x-layouts.app>
    <x-slot:title>Subject Assignments – Faculty Portal</x-slot:title>

    <x-page-header
        title="Subject Assignments"
        subtitle="Create, edit, and evaluate coursework and assignments for your subject offerings."
    >
        <x-slot:actions>
            <a href="{{ route('instructor.assignments.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] px-4 py-2.5 text-xs sm:text-sm font-semibold text-white border border-gold/40 hover:border-gold/70 shadow-sm transition">
                <x-icon name="plus" class="h-4 w-4 text-gold" />
                <span>+ Create Assignment</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    @if ($assignments->isEmpty())
        <x-card>
            <x-empty-state
                title="No assignments created"
                text="You haven't posted any assignments yet. Click '+ Create Assignment' to add your first coursework task."
            />
        </x-card>
    @else
        <div class="overflow-hidden rounded-2xl border border-line bg-[#FFFEFA] shadow-2xs">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/80 border-b border-line text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-3.5">Subject</th>
                        <th class="px-5 py-3.5">Assignment Title</th>
                        <th class="px-5 py-3.5">Due Date</th>
                        <th class="px-5 py-3.5">Total Points</th>
                        <th class="px-5 py-3.5">Submissions</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($assignments as $assignment)
                        <tr class="hover:bg-indigo-50/20 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md bg-[#0A192F] px-2 py-0.5 font-mono text-[11px] font-bold text-gold">
                                    {{ $assignment->subject?->subject_code ?? 'CODE' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-serif font-bold text-navy text-sm">
                                    {{ $assignment->title }}
                                </div>
                                @if ($assignment->description)
                                    <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">
                                        {{ $assignment->description }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-600">
                                {{ $assignment->due_date ? $assignment->due_date->format('M d, Y h:i A') : 'No deadline' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap font-mono text-xs font-bold text-navy">
                                {{ $assignment->total_points }} pts
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="text-xs font-bold text-navy">{{ $assignment->submissions_count }}</span>
                                <span class="text-xs text-gray-400">turned in</span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($assignment->pending_count > 0)
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-800 border border-amber-200">
                                        {{ $assignment->pending_count }} need review
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-200">
                                        Graded
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('instructor.assignments.submissions', $assignment) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#0A192F] text-gold hover:bg-[#14365D] text-xs font-bold transition">
                                    <x-icon name="check" class="h-3 w-3" />
                                    <span>Review ({{ $assignment->submissions_count }})</span>
                                </a>
                                <a href="{{ route('instructor.assignments.edit', $assignment) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-gray-50 text-gray-700 hover:text-navy text-xs font-semibold border border-line transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $assignments->links() }}
        </div>
    @endif
</x-layouts.app>
