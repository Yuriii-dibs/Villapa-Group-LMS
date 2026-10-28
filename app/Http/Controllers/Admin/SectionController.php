<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Role;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectOffering;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    private const DAY_MAP = [
        'M' => 'Monday', 'MON' => 'Monday', 'MONDAY' => 'Monday',
        'T' => 'Tuesday', 'TUE' => 'Tuesday', 'TUESDAY' => 'Tuesday',
        'W' => 'Wednesday', 'WED' => 'Wednesday', 'WEDNESDAY' => 'Wednesday',
        'TH' => 'Thursday', 'THU' => 'Thursday', 'THURSDAY' => 'Thursday',
        'F' => 'Friday', 'FRI' => 'Friday', 'FRIDAY' => 'Friday',
        'S' => 'Saturday', 'SAT' => 'Saturday', 'SATURDAY' => 'Saturday',
        'SU' => 'Sunday', 'SUN' => 'Sunday', 'SUNDAY' => 'Sunday',
    ];

    /**
     * Display a listing of institutional sections and cohorts.
     */
    public function index(Request $request): View
    {
        $sections = Section::withCount(['students', 'offerings'])
            ->orderBy('year_level')
            ->orderBy('name')
            ->get();

        return view('admin.sections.index', compact('sections'));
    }

    /**
     * View and manage the complete weekly schedule of subjects for a specific section.
     */
    public function schedule(Request $request, Section $section): View
    {
        $currentTerm = AcademicTerm::where('is_current', true)->first() ?: AcademicTerm::first();
        $termId = $request->input('term_id', $currentTerm?->id);
        $terms = AcademicTerm::orderByDesc('is_current')->orderByDesc('start_date')->get();

        $offerings = SubjectOffering::with(['subject', 'instructor', 'academicTerm', 'students'])
            ->withCount('students')
            ->where('section_id', $section->id)
            ->when($termId, fn($q) => $q->where('academic_term_id', $termId))
            ->get();

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $scheduleByDay = array_fill_keys($daysOfWeek, []);

        foreach ($offerings as $offering) {
            $subject = $offering->subject;
            if (!$subject) continue;

            $slots = $offering->schedules ?: ($subject->schedules ?: []);
            foreach ($slots as $slot) {
                $rawDay = $slot['day'] ?? '';
                $tokens = array_map('trim', preg_split('/[,\\/]+/', $rawDay));

                foreach ($tokens as $token) {
                    $day = self::DAY_MAP[strtoupper($token)] ?? null;
                    if ($day && isset($scheduleByDay[$day])) {
                        $startTs = strtotime($slot['time_start'] ?? '12:00 AM');
                        $endTs = strtotime($slot['time_end'] ?? '12:00 AM');
                        $durationMin = ($endTs > $startTs) ? round(($endTs - $startTs) / 60) : 0;
                        $durationStr = $durationMin >= 60
                            ? ($durationMin % 60 === 0 ? ($durationMin / 60) . ' hrs' : floor($durationMin / 60) . 'h ' . ($durationMin % 60) . 'm')
                            : ($durationMin > 0 ? $durationMin . ' mins' : '');

                        $scheduleByDay[$day][] = [
                            'offering'     => $offering,
                            'subject'      => $subject,
                            'subject_code' => $subject->subject_code,
                            'title'        => $subject->title,
                            'units'        => $subject->units,
                            'type'         => $slot['type'] ?? 'Lec',
                            'time_start'   => $slot['time_start'] ?? '',
                            'time_end'     => $slot['time_end'] ?? '',
                            'start_time'   => $startTs,
                            'duration'     => $durationStr,
                            'room'         => $slot['room'] ?? ($offering->room ?: 'Campus Room'),
                            'instructor'   => $offering->instructor?->name ?: ($offering->instructor_name ?: ($subject->instructor ?? 'Faculty')),
                            'meet_link'    => $offering->effective_meet_link,
                        ];
                    }
                }
            }
        }

        // Sort each day chronologically
        foreach ($scheduleByDay as $day => $items) {
            usort($scheduleByDay[$day], fn($a, $b) => $a['start_time'] <=> $b['start_time']);
        }

        $totalSessions = array_sum(array_map('count', $scheduleByDay));
        $subjects = Subject::orderBy('subject_code')->get();
        $instructors = User::whereHas('roles', fn($q) => $q->where('name', Role::INSTRUCTOR))->orderBy('name')->get();

        $subjectsData = $subjects->map(function ($s) {
            $assigned = $s->assignedInstructorUser();

            $normalizedSlots = [];
            if (!empty($s->schedules)) {
                foreach ($s->schedules as $slot) {
                    $rawDay = $slot['day'] ?? '';
                    $tokens = array_map('trim', preg_split('/[,\\/]+/', $rawDay));
                    foreach ($tokens as $token) {
                        $fullDay = self::DAY_MAP[strtoupper($token)] ?? ($slot['day'] ?? 'Monday');
                        $normalizedSlots[] = [
                            'day'        => $fullDay,
                            'time_start' => $slot['time_start'] ?? '',
                            'time_end'   => $slot['time_end'] ?? '',
                            'room'       => $slot['room'] ?? 'Campus Room',
                            'type'       => $slot['type'] ?? 'Lec',
                        ];
                    }
                }
            }

            return [
                'id'              => $s->id,
                'subject_code'    => $s->subject_code,
                'title'           => $s->title,
                'units'           => $s->units,
                'instructor_name' => $s->instructor,
                'instructor_id'   => $assigned?->id,
                'meet_link'       => $s->meet_link,
                'schedules'       => $normalizedSlots,
            ];
        });

        return view('admin.sections.schedule', compact(
            'section',
            'offerings',
            'scheduleByDay',
            'terms',
            'termId',
            'currentTerm',
            'totalSessions',
            'subjects',
            'subjectsData',
            'instructors'
        ));
    }

    /**
     * Create and schedule a subject offering directly for this section.
     */
    public function storeSchedule(Request $request, Section $section): RedirectResponse
    {
        $validated = $request->validate([
            'academic_term_id' => ['required', 'exists:academic_terms,id'],
            'subject_id'       => ['required', 'exists:subjects,id'],
            'instructor_id'    => ['nullable', 'exists:users,id'],
            'instructor_name'  => ['nullable', 'string', 'max:255'],
            'room'             => ['nullable', 'string', 'max:100'],
            'meet_link'        => ['nullable', 'url', 'max:255'],
            'slots'            => ['nullable', 'array'],
            'force'            => ['nullable', 'boolean'],
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);
        $assignedUser = $subject->assignedInstructorUser();
        $instructorId = $validated['instructor_id'] ?: $assignedUser?->id;
        $instructorName = $validated['instructor_name'] ?? null;
        if (!empty($instructorId) && empty($instructorName)) {
            $instructorName = User::find($instructorId)?->name;
        }
        if (empty($instructorName)) {
            $instructorName = $assignedUser?->name ?: $subject->instructor;
        }

        $schedules = [];
        if (!empty($request->input('slots'))) {
            foreach ($request->input('slots') as $slot) {
                if (!empty($slot['day']) && !empty($slot['time_start'])) {
                    $schedules[] = [
                        'day'        => $slot['day'],
                        'time_start' => $slot['time_start'],
                        'time_end'   => $slot['time_end'] ?? '',
                        'room'       => $slot['room'] ?? ($validated['room'] ?? ($subject->schedules[0]['room'] ?? 'Campus Room')),
                        'type'       => $slot['type'] ?? 'Lec',
                    ];
                }
            }
        }

        // Auto-populate from subject catalog if no custom slots provided
        if (empty($schedules) && !empty($subject->schedules)) {
            foreach ($subject->schedules as $slot) {
                $rawDay = $slot['day'] ?? '';
                $tokens = array_map('trim', preg_split('/[,\\/]+/', $rawDay));
                foreach ($tokens as $token) {
                    $day = self::DAY_MAP[strtoupper($token)] ?? $token;
                    if (!empty($day) && !empty($slot['time_start'])) {
                        $schedules[] = [
                            'day'        => $day,
                            'time_start' => $slot['time_start'],
                            'time_end'   => $slot['time_end'] ?? '',
                            'room'       => $slot['room'] ?? ($validated['room'] ?? 'Campus Room'),
                            'type'       => $slot['type'] ?? 'Lec',
                        ];
                    }
                }
            }
        }

        // Check for time conflicts with existing offerings in this section & term (excluding self)
        if (!$request->boolean('force') && !empty($schedules)) {
            $existingOfferings = SubjectOffering::with('subject')
                ->where('section_id', $section->id)
                ->where('academic_term_id', $validated['academic_term_id'])
                ->where('subject_id', '!=', $subject->id)
                ->get();

            $conflicts = [];
            foreach ($schedules as $newSlot) {
                $newDay = self::DAY_MAP[strtoupper($newSlot['day'])] ?? $newSlot['day'];
                $newStart = strtotime($newSlot['time_start'] ?? '');
                $newEnd = strtotime($newSlot['time_end'] ?? '');

                if (!$newStart || !$newEnd) continue;

                foreach ($existingOfferings as $exOff) {
                    $exSubj = $exOff->subject?->subject_code ?? 'Class';
                    foreach ($exOff->schedules ?? [] as $exSlot) {
                        $exDay = self::DAY_MAP[strtoupper($exSlot['day'] ?? '')] ?? ($exSlot['day'] ?? '');
                        if ($exDay === $newDay) {
                            $exStart = strtotime($exSlot['time_start'] ?? '');
                            $exEnd = strtotime($exSlot['time_end'] ?? '');

                            if ($exStart && $exEnd && max($newStart, $exStart) < min($newEnd, $exEnd)) {
                                $conflicts[] = "{$newSlot['day']} ({$newSlot['time_start']} - {$newSlot['time_end']}) overlaps with {$exSubj} ({$exSlot['time_start']} - {$exSlot['time_end']})";
                            }
                        }
                    }
                }
            }

            if (!empty($conflicts)) {
                return back()
                    ->withInput()
                    ->with('warning', 'Schedule conflict detected for Section ' . $section->name . ': ' . implode('; ', $conflicts) . ". If intentional, check 'Override Conflict' and submit again.");
            }
        }

        $offering = SubjectOffering::updateOrCreate(
            [
                'academic_term_id' => $validated['academic_term_id'],
                'section_id'       => $section->id,
                'subject_id'       => $subject->id,
            ],
            [
                'instructor_id'    => $instructorId ?: null,
                'instructor_name'  => $instructorName,
                'room'             => $validated['room'] ?? null,
                'meet_link'        => $validated['meet_link'] ?? $subject->meet_link,
                'schedules'        => $schedules,
                'status'           => 'active',
            ]
        );

        return back()->with('status', "Scheduled [{$subject->subject_code}] {$subject->title} for Section {$section->name} successfully.");
    }

    /**
     * Store a new section.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:50', 'unique:sections,name'],
            'program'    => ['required', 'string', 'max:50'],
            'year_level' => ['required', 'integer', 'between:1,5'],
        ]);

        $section = Section::create($validated);

        return back()->with('status', "Section {$section->name} created successfully.");
    }

    /**
     * Update an existing section.
     */
    public function update(Request $request, Section $section): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:50', 'unique:sections,name,' . $section->id],
            'program'    => ['required', 'string', 'max:50'],
            'year_level' => ['required', 'integer', 'between:1,5'],
        ]);

        $section->update($validated);

        return back()->with('status', "Section {$section->name} updated.");
    }

    /**
     * Delete a section.
     */
    public function destroy(Section $section): RedirectResponse
    {
        $name = $section->name;
        $section->delete();

        return back()->with('status', "Section {$name} deleted.");
    }
}
