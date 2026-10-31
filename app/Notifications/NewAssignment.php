<?php

namespace App\Notifications;

use App\Models\SubjectAssignment;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NewAssignment extends Notification
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
     * Notify every student enrolled in an offering of the assignment's subject
     * (the same students who can see the assignment in their Assignments list).
     *
     * Returns how many students were notified. A failure here must never block
     * creating the assignment, so errors are only logged.
     */
    public static function notifyStudents(SubjectAssignment $assignment): int
    {
        try {
            $assignment->loadMissing('subject');

            $studentIds = DB::table('offering_enrollments')
                ->join('subject_offerings', 'subject_offerings.id', '=', 'offering_enrollments.subject_offering_id')
                ->where('subject_offerings.subject_id', $assignment->subject_id)
                ->where('offering_enrollments.status', '!=', 'dropped')
                ->pluck('offering_enrollments.user_id')
                ->unique()
                ->values();

            if ($studentIds->isEmpty()) {
                return 0;
            }

            $students = User::whereIn('id', $studentIds)->get();

            $subjectLabel = $assignment->subject
                ? trim($assignment->subject->subject_code . ' - ' . $assignment->subject->title, ' -')
                : 'your subject';
            $due = $assignment->due_date
                ? 'Due ' . $assignment->due_date->format('M d, Y g:i A')
                : 'No due date';

            NotificationFacade::send($students, new self([
                'key'     => 'assignment:' . $assignment->id,
                'title'   => 'New assignment: ' . $assignment->title,
                'message' => "{$subjectLabel}. {$due}. {$assignment->total_points} pts.",
                'url'     => route('student.assignments.show', $assignment),
                'count'   => 1,
            ]));

            return $students->count();
        } catch (\Throwable $e) {
            Log::warning('Assignment notification failed: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Mark this assignment's notification as read (called when the student opens it).
     */
    public static function clear(User $user, int $assignmentId): void
    {
        try {
            $user->unreadNotifications()
                ->where('type', self::class)
                ->get()
                ->filter(fn ($n) => ($n->data['key'] ?? null) === 'assignment:' . $assignmentId)
                ->each->markAsRead();
        } catch (\Throwable $e) {
            Log::warning('Clearing assignment notification failed: ' . $e->getMessage());
        }
    }
}
