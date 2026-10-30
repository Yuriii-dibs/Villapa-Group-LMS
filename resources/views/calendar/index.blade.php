<x-layouts.app>
    <x-slot:title>Academic Calendar - Villapa LMS</x-slot:title>

    @php
        $daysInMonth = $currentDate->daysInMonth;
        $startDayOfWeek = $currentDate->copy()->startOfMonth()->dayOfWeek; // 0 = Sunday
        $monthDays = [];

        // Pre-group items by day of month (1..31)
        $itemsByDay = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $itemsByDay[$d] = [];
        }
        foreach ($monthItems as $item) {
            if (! $item->starts_at) {
                continue;
            }
            $dayNum = (int) $item->starts_at->format('j');
            if (isset($itemsByDay[$dayNum])) {
                $itemsByDay[$dayNum][] = $item;
            }
        }

        $todayYear = now()->year;
        $todayMonth = now()->month;
        $todayDay = now()->day;
        $isCurrentMonth = ($year === $todayYear && $month === $todayMonth);
    @endphp

    <div class="space-y-6"
         x-data="{
             openCreateModal: false,
             selectedDay: {{ $isCurrentMonth ? $todayDay : 1 }},
             selectedItems: [],
             allDaysItems: {!! $itemsByDayJsJson !!},
             selectDay(day) {
                 this.selectedDay = day;
                 this.selectedItems = this.allDaysItems[day] || [];
             },
             init() {
                 this.selectDay({{ $isCurrentMonth ? $todayDay : 1 }});
             }
         }">

        <!-- Top Header & Controls -->
        <div class="bg-[#FFFEFA] shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-serif font-semibold text-navy tracking-tight">Academic Calendar</h1>
                </div>
                <p class="text-sm text-gray-500 mt-1">Events, subject schedules, and institutional happenings.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center bg-[#FAF8F2] rounded-lg p-1 space-x-1 border border-line/90">
                    <a href="{{ route('calendar.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
                       class="px-3 py-1.5 text-xs font-medium text-navy hover:bg-[#FFFEFA] rounded-md transition shadow-2xs">
                        &larr; Prev
                    </a>
                    <span class="px-4 py-1.5 text-sm font-serif font-semibold text-navy">
                        {{ $currentDate->format('F Y') }}
                    </span>
                    <a href="{{ route('calendar.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
                       class="px-3 py-1.5 text-xs font-medium text-navy hover:bg-[#FFFEFA] rounded-md transition shadow-2xs">
                        Next &rarr;
                    </a>
                </div>
                @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <button type="button"
                        @click="openCreateModal = true"
                        class="px-4 py-2 bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white border border-gold/40 rounded-lg text-xs sm:text-sm font-medium hover:border-gold/70 shadow-sm transition">
                    + Add Event
                </button>
                @endif
            </div>
        </div>

        <!-- Desktop Month Grid + Day Detail Side Panel (hidden on mobile) -->
        <div class="hidden md:grid md:grid-cols-3 gap-6">
            <!-- 7-Column Month Grid (2 cols) -->
            <div class="md:col-span-2 bg-[#FFFEFA] rounded-xl shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-serif font-semibold text-navy">{{ $currentDate->format('F Y') }}</h2>
                    <span class="text-xs text-gray-400">Click any day to view details</span>
                </div>

                <!-- Day Names Header -->
                <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold uppercase tracking-wider text-gray-400 pb-2 border-b border-gray-100">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                </div>

                <!-- Calendar Cells -->
                <div class="grid grid-cols-7 gap-1.5">
                    {{-- Empty offset cells before day 1 --}}
                    @for ($i = 0; $i < $startDayOfWeek; $i++)
                        <div class="h-24 bg-gray-50/50 rounded-xl p-1.5 border border-dashed border-gray-100 opacity-40"></div>
                    @endfor

                    {{-- Day Cells --}}
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $isToday = $isCurrentMonth && ($day === $todayDay);
                            $dayEvents = $itemsByDay[$day];
                        @endphp
                        <div @click="selectDay({{ $day }})"
                             :class="selectedDay === {{ $day }} ? 'ring-2 ring-indigo-600 bg-indigo-50/30' : 'hover:bg-gray-50'"
                             class="h-24 rounded-xl p-2 border border-gray-200 cursor-pointer transition flex flex-col justify-between overflow-hidden {{ $isToday ? 'bg-amber-50/40 border-amber-300' : 'bg-white' }}">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-black {{ $isToday ? 'inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white' : 'text-gray-700' }}">
                                    {{ $day }}
                                </span>
                                @if (count($dayEvents) > 0)
                                    <span class="text-[10px] font-bold text-gray-400">{{ count($dayEvents) }}</span>
                                @endif
                            </div>

                            <!-- Small colored badge pills for events -->
                            <div class="space-y-1 overflow-hidden">
                                @foreach (array_slice($dayEvents, 0, 2) as $ev)
                                    <div class="text-[10px] font-semibold truncate px-1.5 py-0.5 rounded
                                        {{ $ev->type === 'online_class' ? 'bg-rose-100 text-rose-800' : ($ev->type === 'personal' ? 'bg-purple-100 text-purple-800' : ($ev->type === 'exam' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800')) }}">
                                        {{ $ev->title }}
                                    </div>
                                @endforeach
                                @if (count($dayEvents) > 2)
                                    <div class="text-[9px] text-gray-400 font-bold px-1">+{{ count($dayEvents) - 2 }} more</div>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Side Panel: Selected Day Details -->
            <div class="bg-[#FFFEFA] rounded-xl shadow-[0_2px_14px_rgba(10,25,47,0.03)] border border-line/80 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-line/60 mb-4">
                        <div>
                            <span class="text-[11px] font-medium uppercase tracking-[0.14em] text-gold-dark">Day Overview</span>
                            <h3 class="text-lg font-serif font-semibold text-navy" x-text="'Day ' + selectedDay + ', {{ $currentDate->format('F Y') }}'"></h3>
                        </div>
                        <span class="text-xs px-2.5 py-1 bg-[#FAF8F2] text-navy border border-line/80 font-medium rounded-full" x-text="selectedItems.length + ' item(s)'"></span>
                    </div>

                    <!-- Selected Day Event List -->
                    <template x-if="selectedItems.length === 0">
                        <div class="py-12 text-center text-gray-400">
                            <p class="text-sm font-semibold">No events scheduled on this day.</p>
                            <p class="text-xs mt-1">Use the "+ Add Event" button to record tasks or reminders.</p>
                        </div>
                    </template>

                    <div class="space-y-3 overflow-y-auto max-h-[380px] pr-1">
                        <template x-for="item in selectedItems" :key="item.id">
                            <div class="p-3.5 rounded-xl border border-gray-200 bg-gray-50 flex flex-col justify-between space-y-2">
                                <div class="flex justify-between items-start">
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded"
                                          :class="{
                                              'bg-rose-100 text-rose-800': item.type === 'online_class',
                                              'bg-purple-100 text-purple-800': item.type === 'personal',
                                              'bg-amber-100 text-amber-800': item.type === 'exam',
                                              'bg-blue-100 text-blue-800': item.type !== 'online_class' && item.type !== 'personal' && item.type !== 'exam'
                                          }"
                                          x-text="item.type.replace('_', ' ')">
                                    </span>
                                    <span class="text-xs text-gray-400 font-semibold" x-text="new Date(item.starts_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900" x-text="item.title"></h4>
                                    <template x-if="item.course_title">
                                        <p class="text-xs text-indigo-600 font-semibold" x-text="item.course_title"></p>
                                    </template>
                                    <template x-if="item.description">
                                        <p class="text-xs text-gray-500 mt-1 italic" x-text="item.description"></p>
                                    </template>
                                </div>
                                <div class="pt-2 border-t border-gray-200/60 flex justify-between items-center">
                                    <template x-if="item.is_session">
                                        <a :href="item.session_url" class="px-3 py-1 bg-rose-600 text-white rounded-lg text-xs font-bold hover:bg-rose-700 transition">
                                            Join Class &rarr;
                                        </a>
                                    </template>
                                    <template x-if="!item.is_session && item.can_delete && {{ auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() ? 'true' : 'false' }}">
                                        <form method="POST" :action="'/calendar/events/' + item.raw_id" class="inline" onsubmit="return confirm('Remove this event from calendar?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-bold">
                                                Delete Event
                                            </button>
                                        </form>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile View (Day-by-Day List to prevent horizontal scrolling on 360px) -->
        <div class="block md:hidden bg-white rounded-2xl shadow-sm border border-gray-200 p-4 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h2 class="text-base font-black text-gray-900">Agenda for {{ $currentDate->format('F Y') }}</h2>
                <span class="text-xs text-gray-400">{{ $monthItems->count() }} total events</span>
            </div>

            @if ($monthItems->isEmpty())
                <div class="py-8 text-center text-gray-400 text-sm">
                    No scheduled events this month.
                </div>
            @else
                <div class="divide-y divide-gray-100 space-y-3">
                    @foreach ($monthItems as $item)
                        @continue(! $item->starts_at)
                        <div class="pt-3 flex flex-col space-y-1.5">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-gray-900">
                                    {{ $item->starts_at->format('D, M d') }} &bull; {{ $item->starts_at->format('g:i A') }}
                                </span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded
                                    {{ $item->type === 'online_class' ? 'bg-rose-100 text-rose-800' : ($item->type === 'personal' ? 'bg-purple-100 text-purple-800' : ($item->type === 'exam' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800')) }}">
                                    {{ str_replace('_', ' ', $item->type) }}
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-gray-800">{{ $item->title }}</h4>
                            @if ($item->course_title)
                                <p class="text-xs text-gray-500">{{ $item->course_title }}</p>
                            @endif
                            <div class="flex items-center space-x-3 pt-1">
                                @if ($item->is_session)
                                    <a href="{{ $item->session_url }}" class="px-3 py-1 bg-rose-600 text-white rounded text-xs font-bold hover:bg-rose-700">
                                        Join &rarr;
                                    </a>
                                @elseif ($item->can_delete && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                    <form method="POST" action="{{ route('calendar.events.destroy', $item->raw_id) }}" class="inline" onsubmit="return confirm('Delete this event?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 font-bold hover:text-red-800">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Upcoming 7-Day List (Beside/Below Grid) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-black text-gray-900 mb-4 flex items-center">
                <x-icon name="calendar" class="w-5 h-5 mr-2 text-indigo-600 inline" /> Upcoming Next 7 Days
            </h2>

            @if ($upcomingItems->isEmpty())
                <div class="p-8 text-center text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <p class="text-sm font-semibold text-gray-700">No events in the next 7 days</p>
                    <p class="text-xs text-gray-400 mt-1">Your schedule for the upcoming week is clear.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($upcomingItems as $upItem)
                        @continue(! $upItem->starts_at)
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex flex-col justify-between space-y-2">
                            <div>
                                <div class="flex justify-between items-start">
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded
                                        {{ $upItem->type === 'online_class' ? 'bg-rose-100 text-rose-800' : ($upItem->type === 'personal' ? 'bg-purple-100 text-purple-800' : ($upItem->type === 'exam' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800')) }}">
                                        {{ str_replace('_', ' ', $upItem->type) }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-semibold">
                                        {{ $upItem->starts_at->format('M d, g:i A') }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-gray-900 mt-2">{{ $upItem->title }}</h4>
                                @if ($upItem->course_title)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $upItem->course_title }}</p>
                                @endif
                            </div>
                            @if ($upItem->is_session)
                                <div class="pt-2 border-t border-gray-200">
                                    <a href="{{ $upItem->session_url }}" class="inline-block px-3 py-1 bg-rose-600 text-white rounded-lg text-xs font-bold hover:bg-rose-700 transition">
                                        Join Session &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Add Event Modal -->
        <div x-show="openCreateModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                <div x-show="openCreateModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-navy/60 transition-opacity"
                     @click="openCreateModal = false"></div>

                <div x-show="openCreateModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block align-bottom bg-white rounded-2xl p-6 text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full space-y-4 sm:align-middle">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-black text-gray-900">Add Calendar Event</h3>
                        <button @click="openCreateModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold transition">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('calendar.events.store') }}" class="space-y-4" x-data="{ eventType: 'personal' }">
                        @csrf

                    <div>
                        <label for="event_title" class="block text-xs font-semibold text-gray-700 uppercase">Title</label>
                        <input type="text" id="event_title" name="title" required placeholder="Event or reminder title..." class="mt-1 w-full border-gray-300 rounded-xl text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="event_type" class="block text-xs font-semibold text-gray-700 uppercase">Event Type</label>
                        <select id="event_type" name="type" x-model="eventType" required class="mt-1 w-full border-gray-300 rounded-xl text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="personal">Campus Event / Reminder</option>
                            @if ($canCreateCourseEvent)
                                <option value="class">Subject Class Schedule</option>
                                <option value="exam">Subject Exam / Milestone</option>
                            @endif
                            @if ($canCreateGeneral)
                                <option value="general">Institutional General Event</option>
                            @endif
                        </select>
                    </div>

                    <!-- Course Selector for class/exam types -->
                    <template x-if="eventType === 'class' || eventType === 'exam'">
                        <div>
                            <label for="event_course" class="block text-xs font-semibold text-gray-700 uppercase">Course</label>
                            <select id="event_course" name="course_id" required class="mt-1 w-full border-gray-300 rounded-xl text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select a course you teach...</option>
                                @foreach ($myCourses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="event_start" class="block text-xs font-semibold text-gray-700 uppercase">Starts At</label>
                            <input type="datetime-local" id="event_start" name="starts_at" required
                                   min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                                   value="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                                   class="mt-1 w-full border-gray-300 rounded-xl text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="event_end" class="block text-xs font-semibold text-gray-700 uppercase">Ends At (Optional)</label>
                            <input type="datetime-local" id="event_end" name="ends_at"
                                   min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                                   class="mt-1 w-full border-gray-300 rounded-xl text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label for="event_desc" class="block text-xs font-semibold text-gray-700 uppercase">Description (Optional)</label>
                        <textarea id="event_desc" name="description" rows="3" placeholder="Additional notes or room details..." class="mt-1 w-full border-gray-300 rounded-xl text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="openCreateModal = false" class="px-4 py-2 border rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition shadow-sm">
                            Save Event
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</x-layouts.app>
