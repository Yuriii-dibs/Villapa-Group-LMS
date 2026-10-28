<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PROGRAM_MAP = [
        'College of Information Technology'     => 'BSIT',
        'College of Criminology'                => 'BSCrim',
        'College of Health Sciences'            => 'BSMidwife',
        'College of Midwifery'                  => 'BSMidwife',
        'College of Education'                  => 'BEEd',
        'College of Business & Accountancy'     => 'BSA',
    ];

    private const DAY_NORMALIZER = [
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
     * Display the Student dashboard with announcements, 7-day upcoming classes, and assignments due soon.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load('program', 'section');
        $programCode = $user->program ? (self::PROGRAM_MAP[$user->program->name] ?? null) : null;

        // 1. Enrolled Subjects strictly from SubjectOfferings enrolled by Admin
        $offerings = $user->enrolledOfferings()
            ->with([
                'subject.assignments.submissions' => fn($q) => $q->where('user_id', $user->id),
                'section',
                'academicTerm',
                'instructor',
            ])
            ->get();

        $subjects = $offerings->map(function ($offering) {
            $subj = $offering->subject;
            if (!$subj) return null;

            $subj->current_offering = $offering;
            if (!empty($offering->instructor_name)) {
                $subj->instructor = $offering->instructor_name;
            } elseif ($offering->instructor) {
                $subj->instructor = $offering->instructor->name;
            }
            if (!empty($offering->meet_link)) {
                $subj->meet_link = $offering->meet_link;
            }
            if ($offering->schedules) {
                $subj->schedules = $offering->schedules;
            }
            return $subj;
        })->filter()->unique('id')->values();

        $subjectIds = $subjects->pluck('id')->all();
        $offeringIds = $offerings->pluck('id')->all();

        // 2. Announcements for this student (global, section-targeted, or enrolled subject/offering)
        $announcements = Announcement::query()
            ->where(function ($q) use ($subjectIds, $offeringIds, $user) {
                // Global announcements — visible to everyone
                $q->where('target_type', 'global');

                // Section-targeted announcements — visible to students in that section
                if ($user->section_id) {
                    $q->orWhere(function ($sq) use ($user) {
                        $sq->where('target_type', 'section')
                           ->where('section_id', $user->section_id);
                    });
                }

                // Offering/subject-targeted announcements — from enrolled offerings
                if (!empty($offeringIds) || !empty($subjectIds)) {
                    $q->orWhere(function ($sq) use ($offeringIds, $subjectIds) {
                        $sq->where('target_type', 'offering')
                           ->where(function ($inSq) use ($offeringIds, $subjectIds) {
                               $inSq->whereIn('subject_offering_id', $offeringIds)
                                    ->orWhereIn('subject_id', $subjectIds);
                           });
                    });
                }
            })
            ->with(['author.roles', 'subject', 'section', 'subjectOffering.section'])
            ->orderBy('is_pinned', 'desc')
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 0 ELSE 1 END")
            ->latest('created_at')
            ->take(12)
            ->get();

        // 3. Upcoming Classes (Next 7 Days) strictly from enrolled offerings
        $upcomingClasses = collect();
        $now = now();

        if ($offerings->isNotEmpty()) {
            for ($i = 0; $i < 7; $i++) {
                $date = $now->copy()->addDays($i);
                $dayName = $date->format('l');

                foreach ($offerings as $offering) {
                    $subject = $offering->subject;
                    if (!$subject) continue;

                    $slots = $offering->schedules ?: ($subject->schedules ?: []);
                    foreach ($slots as $slot) {
                        $rawDay = $slot['day'] ?? '';
                        $tokens = array_map('trim', preg_split('/[,\\/]+/', $rawDay));

                        foreach ($tokens as $token) {
                            $normalized = self::DAY_NORMALIZER[strtoupper($token)] ?? null;
                            if ($normalized === $dayName) {
                                $startTimeStr = $slot['time_start'] ?? '08:00 AM';
                                $endTimeStr = $slot['time_end'] ?? '10:00 AM';

                                // Parse datetime for this specific calendar day
                                $startDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $startTimeStr);
                                $endDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $endTimeStr);

                                // 15-minute Google Meet open window
                                $opensAt = $startDateTime->copy()->subMinutes(15);
                                $canJoin = ($now >= $opensAt && $now <= $endDateTime);
                                $isLive = ($now >= $startDateTime && $now <= $endDateTime);
                                $isUpcoming = ($now < $opensAt);
                                $isPassed = ($now > $endDateTime);

                                if (!$isPassed) {
                                    $instructorName = $offering->instructor?->name ?: ($offering->instructor_name ?: ($subject->instructor ?? 'Faculty'));
                                    $roomName = $offering->room ?: ($slot['room'] ?? 'Campus Room');

                                    $upcomingClasses->push([
                                        'offering'        => $offering,
                                        'subject'         => $subject,
                                        'subject_code'    => $subject->subject_code,
                                        'subject_title'   => $subject->title,
                                        'instructor'      => $instructorName,
                                        'room'            => $roomName,
                                        'type'            => $slot['type'] ?? 'Lec',
                                        'meet_link'       => $offering->effective_meet_link,
                                        'is_online'       => str_contains(strtoupper($roomName), 'ONLINE') || !empty($offering->meet_link),
                                        'date'            => $date,
                                        'date_formatted'  => $date->format('D, M d'),
                                        'time_start'      => $startTimeStr,
                                        'time_end'        => $endTimeStr,
                                        'starts_at'       => $startDateTime,
                                        'ends_at'         => $endDateTime,
                                        'is_today'        => $date->isToday(),
                                        'is_tomorrow'     => $date->isTomorrow(),
                                        'is_live'         => $isLive,
                                        'can_join'        => $canJoin,
                                        'is_upcoming'     => $isUpcoming,
                                        'minutes_to_open' => max(0, $now->diffInMinutes($opensAt, false)),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        $upcomingClasses = $upcomingClasses->sortBy('starts_at')->values();

        // 4. Assignments Due Soon across enrolled subjects
        $assignmentsDueSoon = SubjectAssignment::whereIn('subject_id', $subjectIds)
            ->with(['subject', 'submissions' => fn($q) => $q->where('user_id', $user->id)])
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')
            ->take(8)
            ->get();

        $enrolledCount = $subjects->count();
        $submittedCount = SubjectAssignment::whereIn('subject_id', $subjectIds)
            ->whereHas('submissions', fn($q) => $q->where('user_id', $user->id))
            ->count();

        return view('student.dashboard', [
            'user'               => $user,
            'programCode'        => $programCode,
            'enrolledCount'      => $enrolledCount,
            'submittedCount'     => $submittedCount,
            'announcements'      => $announcements,
            'upcomingClasses'    => $upcomingClasses,
            'assignmentsDueSoon' => $assignmentsDueSoon,
        ]);
    }
}
