<x-layouts.app>
    <x-slot:title>Chat Moderation – Admin Console</x-slot:title>

    <div class="space-y-8">
        <x-page-header
            title="Chat Moderation"
            subtitle="Monitor messages across class channels and direct student-instructor conversations."
        >
            <x-slot:actions>
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 border border-line rounded-xl text-xs sm:text-sm font-semibold text-navy bg-white hover:border-gold/50 transition">
                    &larr; Dashboard
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="overflow-hidden rounded-2xl border border-line bg-[#FFFEFA] shadow-2xs">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/80 border-b border-line text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-3.5">Sender</th>
                        <th class="px-5 py-3.5">Channel / Recipient</th>
                        <th class="px-5 py-3.5">Message Content</th>
                        <th class="px-5 py-3.5">Sent At</th>
                        <th class="px-5 py-3.5 text-right">Moderation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($messages as $msg)
                        <tr class="hover:bg-indigo-50/20 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-navy text-xs">
                                    {{ $msg->sender?->name ?? 'Unknown' }}
                                </div>
                                <div class="text-[11px] text-gray-400 font-mono">
                                    {{ $msg->sender?->email }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if ($msg->course)
                                    <span class="rounded bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">
                                        {{ $msg->course->title }}
                                    </span>
                                @elseif ($msg->recipient)
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-700">
                                        DM &rarr; {{ $msg->recipient->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">General</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 max-w-md">
                                <p class="text-xs text-gray-700 leading-relaxed break-words">
                                    {{ $msg->body }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-400">
                                {{ $msg->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.chat-moderation.destroy', $msg) }}" onsubmit="return confirm('Delete this message?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-rose-600 hover:underline">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 italic">No messages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $messages->links() }}
        </div>
    </div>
</x-layouts.app>
