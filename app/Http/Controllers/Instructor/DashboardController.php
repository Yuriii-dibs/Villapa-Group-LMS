<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Role;
use App\Models\SubjectAssignment;
use App\Models\SubjectOffering;
use App\Models\SubjectSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Day mapping for normalizing day tokens.
     */
    private const DAY_MAP = [
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

    /**
     * Display the Instructor dashboard with today's classes, announcements, and assignments needing review.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $todayName = now()->format('l');

        // 1. Instructor's Subject Offerings
        $offerings = SubjectOffering::with(['subject', 'section', 'academicTerm', 'students'])
            ->where(function ($q) use ($user) {
                $q->where('instructor_id', $user->id)
                    ->orWhere('instructor_name', 'like', '%' . $user->name . '%');
            })
            ->get();

        $offeringIds = $offerings->pluck('id');
        $subjectIds = $offerings->pluck('subject_id')->unique();

        // 2. Today's Classes
        $todaysClasses = collect();
        foreach ($offerings as $offering) {
            $subject = $offering->subject;
            if (!$subject) {
                continue;
            }

            $slots = $offering->schedules ?: ($subject->schedules ?: []);
            foreach ($slots as $slot) {
                $rawDay = $slot['day'] ?? '';
                $tokens = array_map('trim', preg_split('/[,\\/]+/', $rawDay));

                foreach ($tokens as $token) {
                    $fullDay = self::DAY_MAP[strtoupper($token)] ?? null;
                    if ($fullDay === $todayName) {
                        $startStr = $slot['time_start'] ?? '08:00 AM';
                        $endStr = $slot['time_end'] ?? '09:00 AM';
                        $startTs = strtotime($startStr);
                        $endTs = strtotime($endStr);

                        $todaysClasses->push([
                            'offering_id'  => $offering->id,
                            'subject_id'   => $subject->id,
                            'subject_code' => $subject->subject_code,
                            'title'        => $subject->title,
                            'section'      => $offering->section?->name ?: $subject->section,
                            'room'         => $offering->room ?: ($slot['room'] ?? 'TBA'),
                            'time_start'   => $startStr,
                            'time_end'     => $endStr,
                            'start_time'   => $startTs,
                            'meet_link'    => $offering->meet_link ?: $subject->meet_link,
                            'is_online'    => str_contains(strtoupper($offering->room ?: ($slot['room'] ?? '')), 'ONLINE') || !empty($offering->meet_link),
                        ]);
                    }
                }
            }
        }
        $todaysClasses = $todaysClasses->sortBy('start_time')->values();

        // 3. Announcements Scoped to Instructor
        $announcements = Announcement::where(function ($q) use ($user, $offeringIds) {
                $q->where('user_id', $user->id)
                  ->orWhere('target_type', 'global')
                  ->orWhereIn('subject_offering_id', $offeringIds);
            })
            ->with(['user', 'subjectOffering.subject'])
            ->latest()
            ->take(6)
            ->get();

        // 4. Assignments Needing Review (submissions with status 'submitted' and grade IS NULL)
        $assignmentsNeedingReview = SubjectSubmission::whereHas('assignment', function ($q) use ($subjectIds) {
                $q->whereIn('subject_id', $subjectIds);
            })
            ->whereNull('grade')
            ->with(['user', 'assignment.subject'])
            ->latest('submitted_at')
            ->take(10)
            ->get();

        $totalPendingReviewCount = SubjectSubmission::whereHas('assignment', function ($q) use ($subjectIds) {
                $q->whereIn('subject_id', $subjectIds);
            })
            ->whereNull('grade')
            ->count();

        // Distinct student count
        $totalStudents = User::whereHas('enrolledOfferings', function ($q) use ($offeringIds) {
            $q->whereIn('subject_offerings.id', $offeringIds);
        })->count();

        $totalAssignmentsCount = SubjectAssignment::whereIn('subject_id', $subjectIds)->count();

        return view('instructor.dashboard', [
            'user'                     => $user,
            'todayName'                => $todayName,
            'offerings'                => $offerings,
            'totalOfferings'           => $offerings->count(),
            'totalStudents'            => $totalStudents,
            'todaysClasses'            => $todaysClasses,
            'announcements'            => $announcements,
            'assignmentsNeedingReview' => $assignmentsNeedingReview,
            'totalPendingReviewCount'  => $totalPendingReviewCount,
            'totalAssignmentsCount'    => $totalAssignmentsCount,
        ]);
    }
}
