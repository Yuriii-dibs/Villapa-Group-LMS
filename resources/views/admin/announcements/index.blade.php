<x-layouts.app>
    <x-slot:title>Announcements Management - Admin Console</x-slot:title>

    <div class="space-y-8">
        <x-page-header
            title="Institutional Announcements"
            subtitle="Publish global announcements, target specific sections, or send notices to individual subject offerings."
        >
            <x-slot:actions>
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 border border-line rounded-xl text-xs sm:text-sm font-semibold text-navy bg-white hover:border-gold/50 transition">
                    &larr; Dashboard
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Add Announcement Form --}}
        <div x-data="{ targetType: 'global' }" class="rounded-2xl border border-line bg-[#FFFEFA] p-6 shadow-2xs space-y-4">
            <h2 class="font-serif text-base font-bold text-navy">Publish New Announcement</h2>

            <form method="POST" action="{{ route('admin.announcements.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Announcement Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Midterm Examination Schedule Announcement"
                               class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Target Audience *</label>
                        <select name="target_type" x-model="targetType" required
                                class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            <option value="global">Global (All Students)</option>
                            <option value="section">By Section</option>
                            <option value="offering">By Subject Offering</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Section Selector --}}
                    <div x-show="targetType === 'section'" x-cloak>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Select Section *</label>
                        <select name="section_id"
                                class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            <option value="">-- Choose Section --</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Offering Selector --}}
                    <div x-show="targetType === 'offering'" x-cloak class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Select Subject Offering *</label>
                        <select name="subject_offering_id"
                                class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold">
                            <option value="">-- Choose Class Offering --</option>
                            @foreach ($offerings as $off)
                                <option value="{{ $off->id }}">
                                    {{ $off->subject?->subject_code }} ({{ $off->section?->name ?: 'Section' }}) - {{ $off->subject?->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Announcement Message *</label>
                    <textarea name="content" rows="3" required placeholder="Type the announcement details for students and faculty..."
                              class="w-full text-xs rounded-xl border border-line bg-white px-3.5 py-2.5 text-navy focus:border-gold focus:ring-1 focus:ring-gold"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div>
                        <select name="priority" class="text-xs rounded-xl border border-line bg-white px-3 py-1.5 text-gray-700">
                            <option value="normal">Normal Priority</option>
                            <option value="urgent">Urgent Notice</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>

                    <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#0A192F] hover:bg-[#14365D] text-gold text-xs font-bold border border-gold/40 transition shadow-2xs">
                        <x-icon name="check" class="h-4 w-4" />
                        <span>Publish Announcement</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Announcements List --}}
        <div class="space-y-4">
            <h2 class="font-serif text-base font-bold text-navy">Published Announcements</h2>

            @if ($announcements->isEmpty())
                <div class="p-8 text-center text-gray-400 bg-white rounded-2xl border border-line text-xs">
                    No announcements published yet.
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($announcements as $ann)
                        <div class="p-5 rounded-2xl border border-line bg-[#FFFEFA] shadow-2xs space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if ($ann->target_type === 'global')
                                        <span class="rounded bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-[10px] font-bold text-indigo-700">
                                            GLOBAL
                                        </span>
                                    @elseif ($ann->target_type === 'section' && $ann->section)
                                        <span class="rounded bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                            SECTION: {{ $ann->section->name }}
                                        </span>
                                    @elseif ($ann->target_type === 'year_level')
                                        <span class="rounded bg-amber-50 border border-amber-200 px-2 py-0.5 text-[10px] font-bold text-amber-800">
                                            YEAR {{ $ann->year_level }} COHORT
                                        </span>
                                    @elseif ($ann->subjectOffering)
                                        <span class="rounded bg-gold/15 border border-gold/40 px-2 py-0.5 text-[10px] font-bold text-navy">
                                            {{ $ann->subjectOffering->subject?->subject_code }} &bull; {{ $ann->subjectOffering->section?->name }}
                                        </span>
                                    @endif

                                    @if ($ann->priority === 'urgent')
                                        <span class="rounded bg-rose-50 border border-rose-200 px-1.5 py-0.5 text-[10px] font-bold text-rose-700">
                                            Urgent
                                        </span>
                                    @endif

                                    <span class="text-xs text-gray-400">
                                        Posted by {{ $ann->user->name }} &bull; {{ $ann->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <form method="POST" action="{{ route('admin.announcements.destroy', $ann) }}" onsubmit="return confirm('Delete this announcement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline">
                                        Delete
                                    </button>
                                </form>
                            </div>

                            <h3 class="font-serif text-base font-bold text-navy">
                                {{ $ann->title }}
                            </h3>
                            <p class="text-xs text-gray-600 leading-relaxed whitespace-pre-wrap">
                                {{ $ann->content }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
