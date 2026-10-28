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

class OfferingController extends Controller
{
    /**
     * Display a listing of course offerings.
     */
    public function index(Request $request): View
    {
        $termId = $request->input('term_id');
        $sectionId = $request->input('section_id');

        $query = SubjectOffering::with(['academicTerm', 'subject', 'section', 'instructor', 'students'])
            ->withCount('students')
            ->latest();

        if ($termId) {
            $query->where('academic_term_id', $termId);
        }

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $offerings = $query->paginate(20)->withQueryString();
        $terms = AcademicTerm::orderByDesc('is_current')->orderByDesc('start_date')->get();
        $sections = Section::orderBy('name')->get();

        return view('admin.offerings.index', compact('offerings', 'terms', 'sections', 'termId', 'sectionId'));
    }

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
     * Show form to create an offering.
     */
    public function create(): View
    {
        $terms = AcademicTerm::orderByDesc('is_current')->get();
        $subjects = Subject::orderBy('subject_code')->get();
        $sections = Section::orderBy('name')->get();
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
                            'room'       => $slot['room'] ?? 'Campus',
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

        return view('admin.offerings.create', compact('terms', 'subjects', 'subjectsData', 'sections', 'instructors'));
    }

    /**
     * Store a newly created offering.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_term_id' => ['required', 'exists:academic_terms,id'],
            'subject_id'       => ['required', 'exists:subjects,id'],
            'section_id'       => ['nullable', 'exists:sections,id'],
            'instructor_id'    => ['nullable', 'exists:users,id'],
            'instructor_name'  => ['nullable', 'string', 'max:255'],
            'room'             => ['nullable', 'string', 'max:100'],
            'meet_link'        => ['nullable', 'url', 'max:255'],
            'slots'            => ['nullable', 'array'],
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

        // Format schedule slots
        $schedules = [];
        if (!empty($request->input('slots'))) {
            foreach ($request->input('slots') as $slot) {
                if (!empty($slot['day']) && !empty($slot['time_start'])) {
                    $schedules[] = [
                        'day'        => $slot['day'],
                        'time_start' => $slot['time_start'],
                        'time_end'   => $slot['time_end'] ?? '',
                        'room'       => $slot['room'] ?? ($validated['room'] ?? 'Campus'),
                        'type'       => $slot['type'] ?? 'Lec',
                    ];
                }
            }
        }

        // Fallback to subject catalog schedules if none provided
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
                            'room'       => $slot['room'] ?? ($validated['room'] ?? 'Campus'),
                            'type'       => $slot['type'] ?? 'Lec',
                        ];
                    }
                }
            }
        }

        $offering = SubjectOffering::create([
            'academic_term_id' => $validated['academic_term_id'],
            'subject_id'       => $validated['subject_id'],
            'section_id'       => $validated['section_id'] ?: null,
            'instructor_id'    => $instructorId ?: null,
            'instructor_name'  => $instructorName,
            'room'             => $validated['room'] ?? null,
            'meet_link'        => $validated['meet_link'] ?? $subject->meet_link,
            'schedules'        => $schedules,
            'status'           => 'active',
        ]);

        return redirect()->route('admin.offerings.index')
            ->with('status', 'Course Offering created for ' . $offering->subject?->subject_code . '.');
    }

    /**
     * Show form to edit an offering.
     */
    public function edit(SubjectOffering $offering): View
    {
        $terms = AcademicTerm::orderByDesc('is_current')->get();
        $subjects = Subject::orderBy('subject_code')->get();
        $sections = Section::orderBy('name')->get();
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
                            'room'       => $slot['room'] ?? 'Campus',
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

        return view('admin.offerings.edit', compact('offering', 'terms', 'subjects', 'subjectsData', 'sections', 'instructors'));
    }

    /**
     * Update an offering.
     */
    public function update(Request $request, SubjectOffering $offering): RedirectResponse
    {
        $validated = $request->validate([
            'academic_term_id' => ['required', 'exists:academic_terms,id'],
            'subject_id'       => ['required', 'exists:subjects,id'],
            'section_id'       => ['nullable', 'exists:sections,id'],
            'instructor_id'    => ['nullable', 'exists:users,id'],
            'instructor_name'  => ['nullable', 'string', 'max:255'],
            'room'             => ['nullable', 'string', 'max:100'],
            'meet_link'        => ['nullable', 'url', 'max:255'],
            'slots'            => ['nullable', 'array'],
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
                        'room'       => $slot['room'] ?? ($validated['room'] ?? 'Campus'),
                        'type'       => $slot['type'] ?? 'Lec',
                    ];
                }
            }
        }

        $offering->update([
            'academic_term_id' => $validated['academic_term_id'],
            'subject_id'       => $validated['subject_id'],
            'section_id'       => $validated['section_id'] ?: null,
            'instructor_id'    => $validated['instructor_id'] ?: null,
            'instructor_name'  => $instructorName,
            'room'             => $validated['room'] ?? null,
            'meet_link'        => $validated['meet_link'] ?? null,
            'schedules'        => $schedules,
        ]);

        return redirect()->route('admin.offerings.index')
            ->with('status', 'Course Offering updated for ' . $offering->subject?->subject_code . '.');
    }

    /**
     * Delete an offering.
     */
    public function destroy(SubjectOffering $offering): RedirectResponse
    {
        $code = $offering->subject?->subject_code;
        $offering->delete();

        return back()->with('status', "Course Offering {$code} deleted.");
    }
}
