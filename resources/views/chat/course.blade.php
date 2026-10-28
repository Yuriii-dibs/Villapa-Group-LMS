<x-layouts.app>
    <x-slot:title>Subject Chat - {{ $course->title }}</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-4" x-data="courseChat({{ $course->id }}, {{ $canSend ? 'true' : 'false' }})">
        <!-- Room Header -->
        <div class="bg-white shadow rounded-lg p-4 flex justify-between items-center">
            <div>
                <a href="{{ route('chat.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">&larr; Back to Channels</a>
                <h1 class="text-xl font-bold text-gray-900 mt-1">{{ $course->title }} - Class Discussion</h1>
                <p class="text-xs text-gray-500">Instructor: {{ $course->instructor->name }} &bull; Live subject chat room</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                Live Polling (3s)
            </span>
        </div>

        <!-- Messages Box -->
        <div class="bg-white shadow rounded-lg p-4 flex flex-col h-[520px]">
            <div id="messages-container" class="flex-1 overflow-y-auto space-y-3 p-2 border border-gray-100 rounded bg-gray-50">
                <template x-if="messages.length === 0">
                    <div class="text-center text-gray-400 py-16 text-sm">
                        No messages yet in this room. Be the first to start the discussion!
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div :class="msg.is_me ? 'flex flex-col items-end' : 'flex flex-col items-start'" class="animate-fade-in transition-all duration-200">
                        <div class="flex items-baseline space-x-2">
                            <span class="text-xs font-bold text-gray-700" x-text="msg.sender_name"></span>
                            <span class="text-[10px] text-gray-400" x-text="msg.created_at"></span>
                        </div>
                        <div :class="msg.is_me ? 'bg-indigo-600 text-white rounded-l-lg rounded-tr-lg' : 'bg-white text-gray-800 border border-gray-200 rounded-r-lg rounded-tl-lg shadow-sm'"
                             class="p-2.5 mt-0.5 max-w-lg text-sm break-words whitespace-pre-wrap"
                             x-text="msg.body">
                        </div>
                    </div>
                </template>
            </div>

            <!-- Send Form -->
            @if ($canSend)
                <div class="mt-3">
                    <template x-if="errorMessage">
                        <div class="p-2 mb-2 bg-red-50 border-l-4 border-red-500 text-red-700 text-xs font-semibold rounded-r" x-text="errorMessage"></div>
                    </template>
                    <form @submit.prevent="sendMessage" class="flex space-x-2">
                        <input type="text"
                               x-model="newMessage"
                               maxlength="1000"
                               placeholder="Type a message (max 1,000 characters)..."
                               :disabled="sending"
                               class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <button type="submit"
                                :disabled="sending || !newMessage.trim()"
                                class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700 disabled:opacity-50 shadow-sm transition inline-flex items-center">
                            <span x-show="!sending">Send</span>
                            <span x-show="sending" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Sending...
                            </span>
                        </button>
                    </form>
                </div>
            @else
                <div class="mt-3 p-2 text-center text-xs text-gray-500 bg-gray-100 rounded">
                    Read-only mode: You are viewing this room with administrator oversight permissions.
                </div>
            @endif
        </div>
    </div>

    <script>
        function courseChat(courseId, canSend) {
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
                    let url = `/chat/courses/${courseId}?after=${this.lastMessageId}`;
                    fetch(url, {
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
                    .catch(err => console.error('Chat poll error:', err));
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
                            throw new Error(errData.message || 'Failed to send message. Please try again.');
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
                        this.errorMessage = err.message || 'Could not send message. Please try again.';
                        this.sending = false;
                    });
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('messages-container');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                }
            }
        }
    </script>
</x-layouts.app>
