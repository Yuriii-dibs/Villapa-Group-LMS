<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewMessage extends Notification
{
    public function __construct(public array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }

    /**
     * Notify $to about a new chat message.
     *
     * To avoid flooding the bell, all unread messages from the same conversation
     * (identified by $key) are merged into ONE notification with a running count.
     * A failure here must never break sending the message itself, so it is only logged.
     */
    public static function send(User $to, string $key, string $title, string $body, string $url): void
    {
        try {
            $preview = Str::limit(trim((string) preg_replace('/\s+/', ' ', $body)), 70);

            $existing = $to->unreadNotifications()
                ->where('type', self::class)
                ->get()
                ->first(fn ($n) => ($n->data['key'] ?? null) === $key);

            if ($existing) {
                $count = ((int) ($existing->data['count'] ?? 1)) + 1;

                $existing->data = [
                    'key'     => $key,
                    'title'   => $title,
                    'message' => "{$count} new messages. Latest: {$preview}",
                    'url'     => $url,
                    'count'   => $count,
                ];
                $existing->created_at = now();
                $existing->save();

                return;
            }

            $to->notify(new self([
                'key'     => $key,
                'title'   => $title,
                'message' => $preview,
                'url'     => $url,
                'count'   => 1,
            ]));
        } catch (\Throwable $e) {
            Log::warning('Message notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Mark the unread notification(s) for a conversation as read
     * (called when the user opens that conversation).
     */
    public static function clear(User $user, string $key): void
    {
        try {
            $user->unreadNotifications()
                ->where('type', self::class)
                ->get()
                ->filter(fn ($n) => ($n->data['key'] ?? null) === $key)
                ->each->markAsRead();
        } catch (\Throwable $e) {
            Log::warning('Clearing message notification failed: ' . $e->getMessage());
        }
    }
}
