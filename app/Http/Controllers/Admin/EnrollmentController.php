<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Role;
use App\Models\Section;
use App\Models\SubjectOffering;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Day normalization map.
     */
    private const DAY_MAP = [
        'M' => 'Monday', 'MON' => 'Monday',
        'T' => 'Tuesday', 'TUE' => 'Tuesday',
        'W' => 'Wednesday', 'WED' => 'Wednesday',
        'TH' => 'Thursday', 'THU' => 'Thursday',
        'F' => 'Friday', 'FRI' => 'Friday',
        'S' => 'Saturday', 'SAT' => 'Saturday',
        'SU' => 'Sunday', 'SUN' => 'Sunday',
    ];

    /**
     * Display the enrollment hub (Section Bulk Enrollment & Irregular Student Offering Picker).
     */
    public function index(Request $request): View
    {
        $currentTerm = AcademicTerm::where('is_current', true)->first() ?: AcademicTerm::first();
        $terms = AcademicTerm::orderByDesc('is_current')->get();
        $sections = Section::withCount('students')->orderBy('name')->get();

        // Selected section for section enrollment & student picker
        $selectedSectionId = $request->input('section_id');
        $selectedSection = null;
        $sectionStudentIds = [];

        if ($selectedSectionId) {
            $selectedSection = Section::with(['students', 'offerings.subject'])->find($selectedSectionId);
            if ($selectedSection) {
                $sectionStudentIds = $selectedSection->students->pluck('id')->toArray();
            }
        }

        // Selected student for irregular offering picker
        $selectedStudentId = $request->input('student_id');
        $selectedStudent = null;
        $enrolledOfferingIds = [];

        if ($selectedStudentId) {
            $selectedStudent = User::with(['section', 'enrolledOfferings'])->find($selectedStudentId);
            if ($selectedStudent) {
                $enrolledOfferingIds = $selectedStudent->enrolledOfferings->pluck('id')->toArray();
            }
        }

        // Available offerings for the current term
        $availableOfferings = SubjectOffering::with(['subject', 'section', 'instructor'])
            ->where('academic_term_id', $currentTerm?->id)
            ->get();

        // All students for dropdown and picker
        $students = User::whereHas('roles', fn($q) => $q->where('name', Role::STUDENT))
            ->with(['section', 'program'])
            ->orderBy('name')
            ->get();

        return view('admin.enrollment.index', compact(
            'currentTerm',
            'terms',
            'sections',
            'students',
            'selectedSection',
            'selectedSectionId',
            'sectionStudentIds',
            'selectedStudent',
            'selectedStudentId',
            'enrolledOfferingIds',
            'availableOfferings'
        ));
    }

    /**
     * Enroll selected students into a section and all its offerings for an academic term.
     */
    public function enrollSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_term_id' => ['required', 'exists:academic_terms,id'],
            'section_id'       => ['required', 'exists:sections,id'],
            'student_ids'      => ['nullable', 'array'],
            'student_ids.*'    => ['exists:users,id'],
        ]);

        $term = AcademicTerm::findOrFail($validated['academic_term_id']);
        $section = Section::findOrFail($validated['section_id']);

        // If specific students are picked, use them and update their section_id
        if (!empty($validated['student_ids'])) {
            $students = User::whereIn('id', $validated['student_ids'])->get();
            User::whereIn('id', $validated['student_ids'])->update(['section_id' => $section->id]);
        } else {
            // Fallback: enroll all students already assigned to this section
            $students = User::where('section_id', $section->id)
                ->whereHas('roles', fn($q) => $q->where('name', Role::STUDENT))
                ->get();
        }

        if ($students->isEmpty()) {
            return back()->with('error', "No students selected or found for Section {$section->name}. Please check at least one student below.");
        }

        // Find offerings for this section and term
        $offerings = SubjectOffering::where('academic_term_id', $term->id)
            ->where('section_id', $section->id)
            ->get();

        if ($offerings->isEmpty()) {
            return back()->with('status', "Assigned {$students->count()} students to Section {$section->name}. Note: No course offerings are currently configured for this section in {$term->name}.");
        }

        $enrolledCount = 0;
        foreach ($students as $student) {
            foreach ($offerings as $offering) {
                $exists = DB::table('offering_enrollments')
                    ->where('user_id', $student->id)
                    ->where('subject_offering_id', $offering->id)
                    ->exists();

                if (!$exists) {
                    DB::table('offering_enrollments')->insert([
                        'user_id'             => $student->id,
                        'subject_offering_id' => $offering->id,
                        'status'              => 'enrolled',
                        'enrolled_at'         => now(),
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                    $enrolledCount++;
                }
            }
        }

        return back()->with('status', "Successfully enrolled {$students->count()} students into Section {$section->name} and {$offerings->count()} offerings ({$enrolledCount} new enrollment records created).");
    }

    /**
     * Enroll an irregular student into selected offerings with time-conflict verification.
     */
    public function enrollStudent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'        => ['required', 'exists:users,id'],
            'section_id'     => ['nullable', 'exists:sections,id'],
            'offering_ids'   => ['nullable', 'array'],
            'offering_ids.*' => ['exists:subject_offerings,id'],
            'force'          => ['nullable', 'boolean'],
        ]);

        $student = User::findOrFail($validated['user_id']);

        // Assign/update section cohort if provided
        if ($request->has('section_id')) {
            $student->section_id = $validated['section_id'] ?: null;
            $student->save();
        }

        $offeringIds = $validated['offering_ids'] ?? [];

        // Check for time conflicts between selected offerings
        $offerings = SubjectOffering::with('subject')->whereIn('id', $offeringIds)->get();
        $conflicts = $this->detectTimeConflicts($offerings);

        if (!empty($conflicts) && !$request->boolean('force')) {
            $conflictDetails = implode(' | ', $conflicts);
            return back()
                ->withInput()
                ->with('warning', "Schedule conflict detected: {$conflictDetails}. Please resolve or check 'Override Conflict'.");
        }

        // Sync enrollments
        $student->enrolledOfferings()->syncWithPivotValues($offeringIds, [
            'status'      => 'enrolled',
            'enrolled_at' => now(),
        ]);

        return back()->with('status', "Enrollment updated for {$student->name} (" . count($offeringIds) . " offerings enrolled).");
    }

    /**
     * Detect overlapping schedules among a collection of offerings.
     */
    private function detectTimeConflicts($offerings): array
    {
        $slots = [];

        foreach ($offerings as $offering) {
            $subjCode = $offering->subject?->subject_code ?? 'SUBJ';
            $scheds = $offering->schedules ?: ($offering->subject?->schedules ?: []);

            foreach ($scheds as $slot) {
                $rawDay = $slot['day'] ?? '';
                $tokens = array_map('trim', preg_split('/[,\\/]+/', $rawDay));

                foreach ($tokens as $token) {
                    $day = self::DAY_MAP[strtoupper($token)] ?? null;
                    if ($day && !empty($slot['time_start']) && !empty($slot['time_end'])) {
                        $start = strtotime($slot['time_start']);
                        $end = strtotime($slot['time_end']);

                        if ($start && $end) {
                            $slots[] = [
                                'offering_id' => $offering->id,
                                'code'        => $subjCode,
                                'day'         => $day,
                                'start'       => $start,
                                'end'         => $end,
                                'start_str'   => $slot['time_start'],
                                'end_str'     => $slot['time_end'],
                            ];
                        }
                    }
                }
            }
        }

        $conflicts = [];
        $count = count($slots);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $s1 = $slots[$i];
                $s2 = $slots[$j];

                if ($s1['offering_id'] !== $s2['offering_id'] && $s1['day'] === $s2['day']) {
                    // Check overlap: max(start1, start2) < min(end1, end2)
                    if ($s1['start'] < $s2['end'] && $s2['start'] < $s1['end']) {
                        $conflicts[] = "{$s1['code']} overlaps with {$s2['code']} on {$s1['day']} ({$s1['start_str']}-{$s1['end_str']} vs {$s2['start_str']}-{$s2['end_str']})";
                    }
                }
            }
        }

        return array_unique($conflicts);
    }
}
