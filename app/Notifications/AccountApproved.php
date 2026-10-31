<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AccountApproved extends Notification
{
    /**
     * @param  string  $roleLabel  "student" or "faculty" (used in the wording only)
     */
    public function __construct(public string $roleLabel = 'student')
    {
    }

    /**
     * Stored in the `notifications` table and shown in the bell bubble
     * the next time the user is logged in.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Account approved',
            'message' => "Your {$this->roleLabel} account has been approved. Welcome to " . config('app.name', 'UEP LMS') . '!',
            'url'     => route($notifiable->dashboardRoute()),
        ];
    }
}
