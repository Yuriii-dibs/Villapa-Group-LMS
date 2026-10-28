<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * Get combined calendar items for a user within a date range.
     */
    public function getItemsForUser(User $user, Carbon $start, Carbon $end): Collection
    {
        // 1. Identify courses the user teaches or is enrolled in
        if ($user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            $courseIds = Course::pluck('id');
        } else {
            $enrolledIds = $user->enrollments()->pluck('course_id');
            $taughtIds = $user->coursesAsInstructor()->pluck('id');
            $courseIds = $enrolledIds->merge($taughtIds)->unique();
        }

        // 2. Fetch Events (course events, personal events, and general events)
        $events = Event::with('course')
            ->where(function ($query) use ($user, $courseIds) {
                // Course events (class, exam)
                $query->whereIn('course_id', $courseIds)
                    // Personal events for this user
                    ->orWhere(function ($q) use ($user) {
                        $q->where('type', Event::TYPE_PERSONAL)
                          ->where('user_id', $user->id);
                    })
                    // General administrative events
                    ->orWhere('type', Event::TYPE_GENERAL);
            })
            ->whereBetween('starts_at', [$start, $end])
            ->get()
            ->map(function ($event) use ($user) {
                $canDelete = false;
                if ($user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
                    $canDelete = true;
                } elseif ($event->type === Event::TYPE_PERSONAL && $event->user_id === $user->id) {
                    $canDelete = true;
                } elseif ($event->course && $user->teachesCourse($event->course)) {
                    $canDelete = true;
                }

                return (object) [
                    'id' => 'event_' . $event->id,
                    'raw_id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'type' => $event->type,
                    'starts_at' => $event->starts_at,
                    'ends_at' => $event->ends_at,
                    'course_title' => $event->course?->title,
                    'course_id' => $event->course_id,
                    'is_session' => false,
                    'session_url' => null,
                    'can_delete' => $canDelete,
                ];
            });

        // 3. Fetch online class sessions directly from class_sessions without duplication
        $sessions = ClassSession::with('course')
            ->whereIn('course_id', $courseIds)
            ->whereBetween('starts_at', [$start, $end])
            ->get()
            ->map(function ($session) {
                return (object) [
                    'id' => 'session_' . $session->id,
                    'raw_id' => $session->id,
                    'title' => '[Live Class] ' . $session->title,
                    'description' => $session->description,
                    'type' => 'online_class',
                    'starts_at' => $session->starts_at,
                    'ends_at' => $session->ends_at,
                    'course_title' => $session->course?->title,
                    'course_id' => $session->course_id,
                    'is_session' => true,
                    'session_url' => route('classes.show', $session->id),
                    'can_delete' => false,
                ];
            });

        // 4. Fetch recurring classes from SubjectOfferings (enrolled or taught)
        $dayMap = [
            'M'         => 'Monday',
            'MON'       => 'Monday',
            'MONDAY'    => 'Monday',
            'T'         => 'Tuesday',
            'TUE'       => 'Tuesday',
            'TUESDAY'   => 'Tuesday',
            'W'         => 'Wednesday',
            'WED'       => 'Wednesday',
            'WEDNESDAY' => 'Wednesday',
            'TH'        => 'Thursday',
            'THU'       => 'Thursday',
            'THURSDAY'  => 'Thursday',
            'F'         => 'Friday',
            'FRI'       => 'Friday',
            'FRIDAY'    => 'Friday',
            'S'         => 'Saturday',
            'SAT'       => 'Saturday',
            'SATURDAY'  => 'Saturday',
            'SU'        => 'Sunday',
            'SUN'       => 'Sunday',
            'SUNDAY'    => 'Sunday',
        ];

        if ($user->hasRole(Role::INSTRUCTOR)) {
            $offerings = \App\Models\SubjectOffering::with(['subject', 'section', 'academicTerm'])
                ->where('instructor_id', $user->id)
                ->orWhere('instructor_name', 'like', '%' . $user->name . '%')
                ->get();
        } else {
            $offerings = $user->enrolledOfferings()->with(['subject', 'section', 'academicTerm'])->get();
        }

        $offeringItems = collect();
        if ($offerings->isNotEmpty()) {
            $cursor = $start->copy()->startOfDay();
            $periodEnd = $end->copy()->endOfDay();

            while ($cursor->lte($periodEnd)) {
                $curDayName = $cursor->format('l');

                foreach ($offerings as $offering) {
                    $subject = $offering->subject;
                    if (!$subject) continue;

                    $slots = $offering->schedules ?: ($subject->schedules ?: []);
                    foreach ($slots as $slot) {
                        $rawDay = $slot['day'] ?? '';
                        $tokens = array_map('trim', preg_split('/[,\\/]+/', $rawDay));

                        foreach ($tokens as $token) {
                            $fullDay = $dayMap[strtoupper($token)] ?? null;
                            if ($fullDay === $curDayName) {
                                $startStr = $slot['time_start'] ?? '08:00 AM';
                                $endStr = $slot['time_end'] ?? '09:00 AM';

                                $slotStart = Carbon::parse($cursor->format('Y-m-d') . ' ' . $startStr);
                                $slotEnd = Carbon::parse($cursor->format('Y-m-d') . ' ' . $endStr);

                                $isOnline = str_contains(strtoupper($offering->room ?: ($slot['room'] ?? '')), 'ONLINE') || !empty($offering->meet_link);

                                $offeringItems->push((object) [
                                    'id' => 'offering_' . $offering->id . '_' . $cursor->format('Ymd') . '_' . substr(md5($startStr), 0, 4),
                                    'raw_id' => $offering->id,
                                    'title' => '[' . $subject->subject_code . '] ' . $subject->title,
                                    'description' => 'Room: ' . ($offering->room ?: 'TBA') . ' | Section: ' . ($offering->section?->name ?: 'N/A'),
                                    'type' => $isOnline ? 'online_class' : 'class',
                                    'starts_at' => $slotStart,
                                    'ends_at' => $slotEnd,
                                    'course_title' => $subject->subject_code . ' (' . ($offering->section?->name ?: 'Section') . ')',
                                    'course_id' => null,
                                    'is_session' => !empty($offering->meet_link),
                                    'session_url' => $offering->meet_link,
                                    'can_delete' => false,
                                ]);
                            }
                        }
                    }
                }

                $cursor->addDay();
            }
        }

        return $events->merge($sessions)->merge($offeringItems)->sortBy('starts_at')->values();
    }

    /**
     * Get combined calendar items for a given month.
     */
    public function getMonthItems(User $user, int $year, int $month): Collection
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $this->getItemsForUser($user, $start, $end);
    }

    /**
     * Get combined calendar items for the next 7 days.
     */
    public function getNextSevenDaysItems(User $user): Collection
    {
        $start = now()->startOfDay();
        $end = now()->addDays(7)->endOfDay();

        return $this->getItemsForUser($user, $start, $end);
    }
}
