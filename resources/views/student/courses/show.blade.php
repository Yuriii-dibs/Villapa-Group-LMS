<x-layouts.app>
    <x-slot:title>{{ $course->title }} - Classroom</x-slot:title>

    <div class="max-w-5xl mx-auto space-y-6">
        @if (session('status'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg text-sm shadow-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Course Header & Progress Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-indigo-50 text-indigo-700">
                            {{ $course->category->name }}
                        </span>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 capitalize">
                            {{ $course->difficulty }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">{{ $course->title }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Instructor: <span class="font-semibold text-gray-800">{{ $course->instructor->name }}</span></p>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="{{ route('chat.course', $course) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                            <x-icon name="chat" class="w-3.5 h-3.5" /> Subject Chat Room
                        </a>
                        <a href="{{ route('courses.show', $course) }}"
                           class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                            View Public Overview
                        </a>
                    </div>
                </div>


                    <div class="flex justify-between items-center text-xs font-bold text-gray-700 mb-1.5">
                        <span>Curriculum Progress</span>
                        <span class="text-indigo-600 text-sm">{{ $progressPercentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                    </div>
                    @if ($enrollment && $enrollment->completed_at)
                        <div class="mt-3 flex items-center gap-1.5 text-xs text-emerald-700 font-bold bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                            <x-icon name="check" class="w-3.5 h-3.5 text-emerald-600" /> Completed on {{ $enrollment->completed_at->format('M d, Y') }}
                        </div>
                    @else
                        <p class="text-xs text-gray-400 mt-2">Finish all lessons to complete this subject.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Course Content Tabs (Syllabus, Chat, Online Classes) -->
        <div x-data="{ activeTab: 'syllabus' }" class="space-y-6">
            <!-- Tabs Navigation -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-2 flex space-x-2">
                <button type="button"
                        @click="activeTab = 'syllabus'"
                        :class="activeTab === 'syllabus' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center space-x-2">
                    <x-icon name="book" class="w-4 h-4" />
                    <span>Syllabus ({{ $course->modules->sum(fn($m) => $m->lessons->count()) }})</span>
                </button>
                <button type="button"
                        @click="activeTab = 'chat'"
                        :class="activeTab === 'chat' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center space-x-2">
                    <x-icon name="chat" class="w-4 h-4" />
                    <span>Subject Chat</span>
                </button>
                <button type="button"
                        @click="activeTab = 'classes'"
                        :class="activeTab === 'classes' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center space-x-2">
                    <x-icon name="video" class="w-4 h-4" />
                    <span>Online Classes ({{ $course->classSessions->count() }})</span>
                </button>
            </div>

            <!-- TAB 1: Lessons & Modules Breakdown -->
            <div x-show="activeTab === 'syllabus'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Subject Syllabus</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Work through modules sequentially or review completed lessons.</p>
                    </div>
                </div>

            <div class="space-y-5">
                @forelse ($course->modules as $module)
                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="bg-gray-50 px-5 py-3.5 font-bold text-gray-800 text-sm border-b border-gray-200 flex justify-between items-center">
                            <span>{{ $module->title }}</span>
                            <span class="text-xs text-gray-500 font-medium">{{ $module->lessons->count() }} lessons</span>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            @forelse ($module->lessons as $lesson)
                                @php
                                    $isDone = in_array($lesson->id, $completedLessonIds, true);
                                @endphp
                                <li class="px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition">
                                    <div class="flex items-center space-x-3.5 min-w-0 pr-4">
                                        @if ($isDone)
                                            <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 text-xs font-bold">
                                                <x-icon name="check" class="w-3.5 h-3.5" />
                                            </span>
                                        @else
                                            <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-400 text-xs font-medium">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                            </span>
                                        @endif
                                        <a href="{{ route('student.lessons.show', [$course, $lesson]) }}"
                                           class="text-sm font-semibold text-gray-900 hover:text-indigo-600 truncate transition">
                                            {{ $lesson->title }}
                                        </a>
                                    </div>
                                    <div class="flex items-center space-x-3 flex-shrink-0">
                                        <a href="{{ route('student.lessons.show', [$course, $lesson]) }}"
                                           class="px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm {{ $isDone ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }}">
                                            {{ $isDone ? 'Review' : 'Start Lesson' }}
                                        </a>
                                    </div>
                                </li>
                            @empty
                                <li class="px-5 py-4 text-xs text-gray-400 italic">No lessons in this module.</li>
                            @endforelse
                        </ul>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 bg-gray-50 rounded-xl">
                        No syllabus modules added yet.
                    </div>
                @endforelse
            </div>

            <!-- TAB 2: Embedded Course Chat Room -->
            <div x-show="activeTab === 'chat'"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8"
                 x-data="courseTabChat({{ $course->id }})">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Subject Discussion Room</h2>
                        <p class="text-xs text-gray-500">Live chat with fellow students and your instructor.</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        Live Polling (3s)
                    </span>
                </div>

                <div class="flex flex-col h-[460px] border border-gray-200 rounded-xl overflow-hidden bg-gray-50">
                    <div id="course-tab-messages" class="flex-1 overflow-y-auto space-y-3 p-4">
                        <template x-if="messages.length === 0">
                            <div class="text-center text-gray-400 py-16 text-sm">
                                No messages yet in this subject room. Send a message below to start the conversation!
                            </div>
                        </template>

                        <template x-for="msg in messages" :key="msg.id">
                            <div :class="msg.is_me ? 'flex flex-col items-end' : 'flex flex-col items-start'">
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-xs font-bold text-gray-700" x-text="msg.sender_name"></span>
                                    <span class="text-[10px] text-gray-400" x-text="msg.created_at"></span>
                                </div>
                                <div :class="msg.is_me ? 'bg-indigo-600 text-white rounded-l-xl rounded-tr-xl' : 'bg-white text-gray-800 border border-gray-200 rounded-r-xl rounded-tl-xl shadow-sm'"
                                     class="p-3 mt-0.5 max-w-lg text-sm break-words whitespace-pre-wrap"
                                     x-text="msg.body">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="p-3 bg-white border-t border-gray-200">
                        <template x-if="errorMessage">
                            <div class="p-2 mb-2 bg-red-50 border-l-4 border-red-500 text-red-700 text-xs font-semibold rounded-r" x-text="errorMessage"></div>
                        </template>
                        <form @submit.prevent="sendMessage" class="flex space-x-2">
                            <input type="text"
                                   x-model="newMessage"
                                   maxlength="1000"
                                   placeholder="Type a message to the class..."
                                   :disabled="sending"
                                   class="flex-1 border-gray-300 rounded-xl shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit"
                                    :disabled="sending || !newMessage.trim()"
                                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 disabled:opacity-50 shadow-sm transition inline-flex items-center">
                                <span x-show="!sending">Send</span>
                                <span x-show="sending">Sending...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Online Classes -->
            <div x-show="activeTab === 'classes'"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Live Video Classes</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Online classroom sessions, lectures, and interactive workshops.</p>
                    </div>
                </div>

                @if ($course->classSessions->isEmpty())
                    <div class="p-12 text-center text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm font-semibold text-gray-700">No online class sessions scheduled</p>
                        <p class="text-xs text-gray-400 mt-1">Your instructor will schedule live meetings here as the curriculum progresses.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-500">
                                <tr>
                                    <th class="px-6 py-3 text-left">Session</th>
                                    <th class="px-6 py-3 text-left">Schedule</th>
                                    <th class="px-6 py-3 text-left">Provider</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th class="px-6 py-3 text-right">Join Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($course->classSessions as $session)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">{{ $session->title }}</div>
                                            @if ($session->description)
                                                <div class="text-xs text-gray-500 italic mt-0.5">{{ $session->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                            <div>{{ $session->starts_at->format('M d, Y') }}</div>
                                            <div class="text-gray-400">{{ $session->starts_at->format('g:i A') }} - {{ $session->ends_at->format('g:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold uppercase">
                                            <span class="px-2 py-0.5 rounded {{ $session->isJitsi() ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $session->provider }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($session->isLive())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 animate-pulse">
                                                    ● LIVE
                                                </span>
                                            @elseif ($session->isEnded())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                                    Ended
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                    Scheduled
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            @if ($session->isLive() || $session->canStudentJoin())
                                                <a href="{{ route('classes.show', $session) }}" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 transition shadow">
                                                    Join Class &rarr;
                                                </a>
                                            @elseif ($session->isEnded())
                                                <span class="text-xs text-gray-400 italic">Session Concluded</span>
                                            @else
                                                <button type="button" disabled class="px-4 py-2 bg-gray-100 text-gray-400 rounded-xl text-xs font-semibold cursor-not-allowed">
                                                    Opens 15m Prior
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function courseTabChat(courseId) {
            return {
                messages: [],
                lastMessageId: 0,
                newMessage: '',
                sending: false,
                errorMessage: '',
                pollTimer: null,

                init() {
                    this.fetchMessages();
                    this.pollTimer = setInterval(() => this.fetchMessages(), 3000);
                },

                fetchMessages() {
                    fetch(`/chat/courses/${courseId}?after=${this.lastMessageId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.messages && data.messages.length > 0) {
                            this.messages = this.messages.concat(data.messages);
                            this.lastMessageId = this.messages[this.messages.length - 1].id;
                            this.scrollToBottom();
                        }
                    })
                    .catch(err => console.error('Course tab chat poll error:', err));
                },

                sendMessage() {
                    if (!this.newMessage.trim() || this.sending) return;
                    this.sending = true;
                    this.errorMessage = '';

                    fetch(`/chat/courses/${courseId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ body: this.newMessage })
                    })
                    .then(async res => {
                        if (!res.ok) {
                            const errData = await res.json().catch(() => ({}));
                            throw new Error(errData.message || 'Failed to send message.');
                        }
                        return res.json();
                    })
                    .then(data => {
                        this.newMessage = '';
                        this.sending = false;
                        if (data.message) {
                            this.messages.push(data.message);
                            this.lastMessageId = data.message.id;
                            this.scrollToBottom();
                        }
                    })
                    .catch(err => {
                        console.error('Send error:', err);
                        this.errorMessage = err.message || 'Could not send message.';
                        this.sending = false;
                    });
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('course-tab-messages');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                }
            }
        }
    </script>
</x-layouts.app>
