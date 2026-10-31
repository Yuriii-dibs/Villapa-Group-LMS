@php
    $bellUser = auth()->user();
    $bellItems = collect();
    $bellUnread = 0;

    try {
        if ($bellUser && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            $bellUnread = $bellUser->unreadNotifications()->count();
            $bellItems = $bellUser->notifications()->latest()->limit(8)->get();
        }
    } catch (\Throwable $e) {
        $bellItems = collect();
        $bellUnread = 0;
    }

    $bellLatest = $bellItems->first(fn ($n) => $n->read_at === null);
@endphp

{{-- Inline styles on purpose: they do not depend on the compiled Tailwind build. --}}
<style>
    .nb-wrap { position: fixed; top: 12px; right: 64px; z-index: 70; }
    @media (min-width: 1024px) { .nb-wrap { top: 18px; right: 28px; } }
    .nb-card {
        position: absolute; top: 52px; right: 0; width: 320px; max-width: calc(100vw - 24px);
        background: #FFFEFA; border: 1px solid #E5DFD3; border-radius: 14px;
        box-shadow: 0 12px 32px rgba(10, 25, 47, .18); overflow: hidden;
    }
</style>

<div class="nb-wrap"
     x-data='{
        panel: false,
        toast: false,
        latest: @json($bellLatest ? $bellLatest->id . '-' . ($bellLatest->data['count'] ?? 1) : null),
        init() {
            if (!this.latest) return;
            try {
                if (sessionStorage.getItem("nb_seen") !== this.latest) {
                    sessionStorage.setItem("nb_seen", this.latest);
                    this.toast = true;
                    setTimeout(() => { this.toast = false; }, 7000);
                }
            } catch (e) {}
        }
     }'
     @keydown.escape.window="panel = false">

    {{-- Bell button with unread count bubble --}}
    <button type="button" @click="panel = !panel; toast = false" aria-label="Notifications"
            style="position:relative;display:flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:9999px;background:#0A192F;border:1px solid #C5A059;color:#C5A059;cursor:pointer;box-shadow:0 4px 12px rgba(10,25,47,.25);">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        @if ($bellUnread > 0)
            <span style="position:absolute;top:-4px;right:-4px;min-width:20px;height:20px;padding:0 5px;border-radius:9999px;background:#DC2626;color:#ffffff;font-size:11px;font-weight:700;line-height:20px;text-align:center;border:2px solid #F7F4EE;">
                {{ $bellUnread > 9 ? '9+' : $bellUnread }}
            </span>
        @endif
    </button>

    {{-- Pop-up bubble (shows once when there is a new unread notification) --}}
    @if ($bellLatest)
        <div x-show="toast" x-cloak x-transition @click="toast = false" class="nb-card" style="padding:14px 16px;cursor:pointer;border-left:4px solid #C5A059;">
            <div style="font-size:13px;font-weight:700;color:#0A192F;">{{ $bellLatest->data['title'] ?? 'Notification' }}</div>
            <div style="margin-top:2px;font-size:12px;line-height:1.4;color:#4B5563;">{{ $bellLatest->data['message'] ?? '' }}</div>
        </div>
    @endif

    {{-- Dropdown list --}}
    <div x-show="panel" x-cloak x-transition @click.outside="panel = false" class="nb-card">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5DFD3;background:#FAF8F5;">
            <span style="font-size:13px;font-weight:700;color:#0A192F;">Notifications</span>
            @if ($bellUnread > 0)
                <form method="POST" action="{{ url('/notifications/read-all') }}">
                    @csrf
                    <button type="submit" style="font-size:11px;font-weight:600;color:#9E7D3B;background:none;border:none;cursor:pointer;">Mark all as read</button>
                </form>
            @endif
        </div>

        <div style="max-height:360px;overflow-y:auto;">
            @forelse ($bellItems as $n)
                @php $unread = $n->read_at === null; @endphp
                <form method="POST" action="{{ url('/notifications/'.$n->id.'/read') }}">
                    @csrf
                    <button type="submit"
                            style="display:block;width:100%;text-align:left;padding:12px 16px;border:none;border-bottom:1px solid #F0EBE0;cursor:pointer;background:{{ $unread ? '#FFF8E7' : '#FFFEFA' }};">
                        <div style="display:flex;align-items:center;gap:8px;">
                            @if ($unread)
                                <span style="flex:none;width:8px;height:8px;border-radius:9999px;background:#DC2626;"></span>
                            @endif
                            <span style="font-size:13px;font-weight:{{ $unread ? '700' : '500' }};color:#0A192F;">{{ $n->data['title'] ?? 'Notification' }}</span>
                        </div>
                        <div style="margin-top:2px;font-size:12px;line-height:1.4;color:#4B5563;">{{ $n->data['message'] ?? '' }}</div>
                        <div style="margin-top:4px;font-size:10px;color:#9CA3AF;">{{ $n->created_at->diffForHumans() }}</div>
                    </button>
                </form>
            @empty
                <div style="padding:28px 16px;text-align:center;font-size:12px;color:#9CA3AF;">No notifications yet.</div>
            @endforelse
        </div>
    </div>
</div>
