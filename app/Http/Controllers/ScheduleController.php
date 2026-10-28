<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Display the student's academic subject schedule based on their assigned program.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            return redirect()->route('admin.sections.index');
        }

        // Map category name → program code (e.g. "College of Information Technology" → "BSIT")
        $programMap = [
            'College of Information Technology'     => 'BSIT',
            'College of Criminology'                => 'BSCrim',
            'College of Health Sciences'            => 'BSMidwife',
            'College of Midwifery'                  => 'BSMidwife',
            'College of Education'                  => 'BEEd',
            'College of Business & Accountancy'     => 'BSA',
        ];

        $user->load('program', 'section');
        $programName = $user->program
            ? ($programMap[$user->program->name] ?? $user->program->name)
            : ($user->section?->program ?? 'BSIT');

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $scheduleByDay = array_fill_keys($daysOfWeek, []);

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

        $isInstructor = $user->hasRole(\App\Models\Role::INSTRUCTOR);

        if ($isInstructor) {
            // Instructor: fetch offerings taught
            $offerings = \App\Models\SubjectOffering::with(['subject', 'section', 'academicTerm', 'instructor'])
                ->where('instructor_id', $user->id)
                ->orWhere('instructor_name', 'like', '%' . $user->name . '%')
                ->get();

            $subjects = $offerings->map(fn($o) => $o->subject)->filter()->unique('id')->values();

            foreach ($offerings as $offering) {
                $subject = $offering->subject;
                if (!$subject) continue;

                $slots = $offering->schedules ?: ($subject->schedules ?: []);
                foreach ($slots as $slot) {
                    $rawDay = $slot['day'] ?? '';
                    $tokens = array_map('trim', preg_split('/[,\\/]+/', $rawDay));

                    foreach ($tokens as $token) {
                        $upperToken = strtoupper($token);
                        $fullDay = $dayMap[$upperToken] ?? (isset($scheduleByDay[ucfirst(strtolower($token))]) ? ucfirst(strtolower($token)) : null);

                        if ($fullDay && isset($scheduleByDay[$fullDay])) {
                            $startTs = strtotime($slot['time_start'] ?? '12:00 AM');
                            $endTs = strtotime($slot['time_end'] ?? '12:00 AM');
                            $durationMin = ($endTs > $startTs) ? round(($endTs - $startTs) / 60) : 0;
                            $durationStr = $durationMin >= 60
                                ? ($durationMin % 60 === 0 ? ($durationMin / 60) . ' hrs' : floor($durationMin / 60) . 'h ' . ($durationMin % 60) . 'm')
                                : ($durationMin > 0 ? $durationMin . ' mins' : '');

                            $scheduleByDay[$fullDay][] = [
                                'subject'         => $subject,
                                'subject_id'      => $subject->id,
                                'offering_id'     => $offering->id,
                                'subject_code'    => $subject->subject_code,
                                'title'           => $subject->title,
                                'type'            => $slot['type'] ?? 'Lec',
                                'time_start'      => $slot['time_start'] ?? '',
                                'time_end'        => $slot['time_end'] ?? '',
                                'start_time'      => $startTs,
                                'duration'        => $durationStr,
                                'room'            => $offering->room ?: ($slot['room'] ?? '—'),
                                'instructor'      => $user->name,
                                'block'           => $subject->block,
                                'section'         => $offering->section?->name ?: $subject->section,
                                'meet_link'       => $offering->meet_link ?: $subject->meet_link,
                                'is_online'       => str_contains(strtoupper($offering->room ?: ($slot['room'] ?? '')), 'ONLINE') || !empty($offering->meet_link),
                            ];
                        }
                    }
                }
            }
        } else {
            // Student: build from enrolled offerings
            $offerings = $user->enrolledOfferings()->with(['subject', 'section', 'academicTerm', 'instructor'])->get();

            if ($offerings->isNotEmpty()) {
                $subjects = $offerings->map(fn($o) => $o->subject)->filter()->unique('id')->values();

                foreach ($offerings as $offering) {
                    $subject = $offering->subject;
                    if (!$subject) continue;

                    $slots = $offering->schedules ?: ($subject->schedules ?: []);
                    foreach ($slots as $slot) {
                        $rawDay = $slot['day'] ?? '';
                        $tokens = array_map('trim', preg_split('/[,\\/]+/', $rawDay));

                        foreach ($tokens as $token) {
                            $upperToken = strtoupper($token);
                            $fullDay = $dayMap[$upperToken] ?? (isset($scheduleByDay[ucfirst(strtolower($token))]) ? ucfirst(strtolower($token)) : null);

                            if ($fullDay && isset($scheduleByDay[$fullDay])) {
                                $startTs = strtotime($slot['time_start'] ?? '12:00 AM');
                                $endTs = strtotime($slot['time_end'] ?? '12:00 AM');
                                $durationMin = ($endTs > $startTs) ? round(($endTs - $startTs) / 60) : 0;
                                $durationStr = $durationMin >= 60
                                    ? ($durationMin % 60 === 0 ? ($durationMin / 60) . ' hrs' : floor($durationMin / 60) . 'h ' . ($durationMin % 60) . 'm')
                                    : ($durationMin > 0 ? $durationMin . ' mins' : '');

                                $scheduleByDay[$fullDay][] = [
                                    'subject'         => $subject,
                                    'subject_id'      => $subject->id,
                                    'offering_id'     => $offering->id,
                                    'subject_code'    => $subject->subject_code,
                                    'title'           => $subject->title,
                                    'type'            => $slot['type'] ?? 'Lec',
                                    'time_start'      => $slot['time_start'] ?? '',
                                    'time_end'        => $slot['time_end'] ?? '',
                                    'start_time'      => $startTs,
                                    'duration'        => $durationStr,
                                    'room'            => $offering->room ?: ($slot['room'] ?? '—'),
                                    'instructor'      => $offering->instructor?->name ?: ($offering->instructor_name ?: $subject->instructor),
                                    'block'           => $subject->block,
                                    'section'         => $offering->section?->name ?: $subject->section,
                                    'meet_link'       => $offering->meet_link ?: $subject->meet_link,
                                    'is_online'       => str_contains(strtoupper($offering->room ?: ($slot['room'] ?? '')), 'ONLINE') || !empty($offering->meet_link),
                                ];
                            }
                        }
                    }
                }
            } else {
                $subjects = collect();
            }
        }

        // Sort each day chronologically by start time
        foreach ($scheduleByDay as $day => $items) {
            usort($scheduleByDay[$day], fn($a, $b) => $a['start_time'] <=> $b['start_time']);
        }

        $totalSessions = array_sum(array_map('count', $scheduleByDay));
        $todayName = now()->format('l');

        return view('schedule.index', compact(
            'subjects',
            'programName',
            'user',
            'scheduleByDay',
            'daysOfWeek',
            'totalSessions',
            'todayName',
            'isInstructor'
        ));
    }
}
