<x-layouts.app>
    <x-slot:title>[{{ $offering->subject?->subject_code ?? 'Class' }}] {{ $offering->subject?->title ?? 'Subject' }} - Group Chat</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-4" x-data="offeringChat({{ $offering->id }}, {{ $canSend ? 'true' : 'false' }})">
        {{-- Room Header Banner --}}
        <div class="rounded-2xl border border-line/80 bg-white p-5 shadow-[0_2px_14px_rgba(10,25,47,0.03)] flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <a href="{{ route('chat.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-navy hover:text-gold transition mb-2">
                    <span>&larr;</span> Back to Messages & Discussions
                </a>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-lg bg-gold/15 px-2.5 py-0.5 font-mono text-xs font-bold text-gold border border-gold/30">
                        {{ $offering->subject?->subject_code ?? 'SUBJ' }}
                    </span>
                    <span class="inline-flex items-center rounded-md bg-[#0A192F] px-2.5 py-0.5 text-xs font-semibold text-paper">
                        {{ $offering->section?->name ?? 'Section' }}
                    </span>
                    @if ($offering->academicTerm)
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">
                            {{ $offering->academicTerm->name }}
                        </span>
                    @endif
                </div>
                <h1 class="font-serif text-xl sm:text-2xl font-bold text-navy mt-1.5">
                    {{ $offering->subject?->title ?? 'Subject Group Chat' }}
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Instructor: <strong>{{ $offering->instructor?->name ?: ($offering->instructor_name ?: ($offering->subject?->instructor ?? 'Faculty')) }}</strong>
                    &bull; Enrolled Class Discussion Room
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @if ($offering->effective_meet_link)
                    <a href="{{ $offering->effective_meet_link }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition">
                        <x-icon name="video" class="h-3.5 w-3.5 text-white" />
                        <span>Google Meet</span>
                    </a>
                @endif
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Room (3s)
                </span>
            </div>
        </div>

        {{-- Messages Container --}}
        <div class="rounded-2xl border border-line/80 bg-white p-5 shadow-[0_2px_14px_rgba(10,25,47,0.03)] flex flex-col h-[540px]">
            <div id="messages-container" class="flex-1 overflow-y-auto space-y-3 p-3 rounded-xl border border-line/60 bg-[#FAF8F5]">
                <template x-if="messages.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center text-gray-400 py-16 text-sm">
                        <x-icon name="chat" class="h-8 w-8 text-gray-300 mb-2" />
                        <p class="font-semibold text-navy">No messages in this subject chat yet.</p>
                        <p class="text-xs text-gray-500 mt-1">Be the first to post a question or announcement to your classmates and professor!</p>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div :class="msg.is_me ? 'flex flex-col items-end' : 'flex flex-col items-start'" class="transition-all duration-150">
                        <div class="flex items-baseline gap-1.5 mb-0.5">
                            <span class="text-xs font-bold text-navy" x-text="msg.sender_name"></span>
                            <span class="text-[10px] font-semibold uppercase px-1.5 py-0.2 rounded"
                                  :class="msg.sender_role === 'Instructor' ? 'bg-indigo-100 text-indigo-800' : (msg.sender_role === 'Admin' ? 'bg-amber-100 text-amber-800' : 'bg-gray-200 text-gray-700')"
                                  x-text="msg.sender_role"></span>
                            <span class="text-[10px] text-gray-400 font-mono" x-text="msg.created_at"></span>
                        </div>
                        <div :class="msg.is_me ? 'bg-gradient-to-r from-[#0B213D] via-[#14365D] to-[#1B4965] text-white rounded-2xl rounded-tr-xs border border-gold/40 shadow-xs' : 'bg-white text-gray-800 border border-line rounded-2xl rounded-tl-xs shadow-2xs'"
                             class="p-3 max-w-xl text-sm break-words whitespace-pre-wrap leading-relaxed"
                             x-text="msg.body">
                        </div>
                    </div>
                </template>
            </div>

            {{-- Message Send Box --}}
            @if ($canSend)
                <div class="mt-4 pt-3 border-t border-line/60">
                    <template x-if="errorMessage">
                        <div class="p-2.5 mb-2 bg-rose-50 border-l-4 border-rose-500 text-rose-800 text-xs font-semibold rounded-r" x-text="errorMessage"></div>
                    </template>
                    <form @submit.prevent="sendMessage" class="flex gap-2.5">
                        <input type="text"
                               x-model="newMessage"
                               maxlength="1000"
                               placeholder="Type a message to your class (max 1,000 characters)..."
                               :disabled="sending"
                               class="flex-1 rounded-xl border border-line bg-[#FFFEFA] px-4 py-2.5 text-sm shadow-2xs focus:border-gold focus:ring-1 focus:ring-gold text-navy placeholder:text-gray-400">
                        <button type="submit"
                                :disabled="sending || !newMessage.trim()"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-[#0A192F] px-5 py-2.5 text-xs font-bold text-gold border border-gold/50 shadow-sm hover:bg-[#14365D] hover:text-white disabled:opacity-40 disabled:cursor-not-allowed transition">
                            <span x-show="!sending">Send</span>
                            <span x-show="sending">Sending...</span>
                            <x-icon name="send" class="h-3.5 w-3.5" />
                        </button>
                    </form>
                </div>
            @else
                <div class="mt-3 p-3 text-center text-xs text-gray-500 bg-gray-100 rounded-xl">
                    Read-only mode: You are viewing this room with administrator oversight permissions.
                </div>
            @endif
        </div>
    </div>

    <script>
        function offeringChat(offeringId, canSend) {
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
                    let url = `/chat/offerings/${offeringId}?after=${this.lastMessageId}`;
                    fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Polling error');
                        return res.json();
                    })
                    .then(data => {
                        if (data.messages && data.messages.length > 0) {
                            this.messages = this.messages.concat(data.messages);
                            this.lastMessageId = data.messages[data.messages.length - 1].id;
                            this.$nextTick(() => {
                                let el = document.getElementById('messages-container');
                                if (el) el.scrollTop = el.scrollHeight;
                            });
                        }
                    })
                    .catch(err => console.error(err));
                },

                sendMessage() {
                    if (!this.newMessage.trim() || this.sending) return;

                    this.sending = true;
                    this.errorMessage = '';

                    fetch(`/chat/offerings/${offeringId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ body: this.newMessage })
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (!res.ok) {
                            if (data.errors && data.errors.body) {
                                throw new Error(data.errors.body[0]);
                            }
                            throw new Error(data.message || 'Failed to send message.');
                        }
                        return data;
                    })
                    .then(data => {
                        this.newMessage = '';
                        this.messages.push(data.message);
                        this.lastMessageId = data.message.id;
                        this.$nextTick(() => {
                            let el = document.getElementById('messages-container');
                            if (el) el.scrollTop = el.scrollHeight;
                        });
                    })
                    .catch(err => {
                        this.errorMessage = err.message;
                    })
                    .finally(() => {
                        this.sending = false;
                    });
                }
            };
        }
    </script>
</x-layouts.app>
