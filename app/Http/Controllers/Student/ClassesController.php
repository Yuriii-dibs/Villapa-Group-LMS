<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\SubjectSubmission;
use App\Notifications\NewAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassesController extends Controller
{
    /**
     * Map category / program names to the subject program codes.
     */
    private const PROGRAM_MAP = [
        'College of Information Technology'     => 'BSIT',
        'College of Criminology'                => 'BSCrim',
        'College of Health Sciences'            => 'BSMidwife',
        'College of Education'                  => 'BEEd',
        'College of Business & Accountancy'     => 'BSA',
    ];

    /**
     * List all enrolled subjects for the student, with upcoming meet links and files.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load('program', 'section');

        $programCode = $user->program ? (self::PROGRAM_MAP[$user->program->name] ?? null) : null;

        // Strictly retrieve offerings explicitly enrolled by administrator
        $offerings = $user->enrolledOfferings()
            ->with([
                'subject.resources',
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

        // Also retrieve standard enrolled courses/class sessions if any
        $enrollments = $user->enrollments()
            ->with([
                'course.instructor',
                'course.classSessions' => fn($q) => $q->orderBy('starts_at'),
                'course.modules.lessons.resources',
            ])
            ->get();

        $upcoming = collect();
        $past     = collect();

        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->course->classSessions as $session) {
                $entry = ['session' => $session, 'course' => $enrollment->course];
                if ($session->isEnded()) {
                    $past->push($entry);
                } else {
                    $upcoming->push($entry);
                }
            }
        }

        $upcoming = $upcoming->sortBy(fn($e) => $e['session']->starts_at);
        $past     = $past->sortByDesc(fn($e) => $e['session']->starts_at);

        return view('student.classes.index', compact('subjects', 'programCode', 'user', 'upcoming', 'past', 'enrollments', 'offerings'));
    }

    /**
     * Display a specific subject's online classroom with Google Meet, files, and assignments.
     */
    public function show(Request $request, Subject $subject): View
    {
        $user = $request->user()->load('program');

        // Check if student is explicitly enrolled in an offering for this subject
        $enrolledOffering = $user->enrolledOfferings()
            ->where('subject_id', $subject->id)
            ->with(['section', 'academicTerm', 'instructor'])
            ->first();

        if (! $enrolledOffering && ! $user->hasRole([\App\Models\Role::ADMIN, \App\Models\Role::SUPER_ADMIN])) {
            abort(403, 'You are not enrolled in this subject offering. Please contact an administrator.');
        }

        if ($enrolledOffering) {
            $subject->current_offering = $enrolledOffering;
            if (!empty($enrolledOffering->instructor_name)) {
                $subject->instructor = $enrolledOffering->instructor_name;
            } elseif ($enrolledOffering->instructor) {
                $subject->instructor = $enrolledOffering->instructor->name;
            }
            if (!empty($enrolledOffering->meet_link)) {
                $subject->meet_link = $enrolledOffering->meet_link;
            }
            if ($enrolledOffering->schedules) {
                $subject->schedules = $enrolledOffering->schedules;
            }
        }

        $subject->load([
            'resources',
            'assignments' => fn($q) => $q->with(['submissions' => fn($sq) => $sq->where('user_id', $user->id)])->latest('due_date'),
        ]);

        return view('student.classes.show', compact('subject', 'user', 'enrolledOffering'));
    }

    /**
     * List all assignments across all enrolled subjects for the student.
     */
    public function assignments(Request $request): View
    {
        $user = $request->user()->load('program');
        $programCode = $user->program ? (self::PROGRAM_MAP[$user->program->name] ?? null) : null;

        $subjectIds = $user->enrolledOfferings()->pluck('subject_id')->filter()->unique()->values();

        $assignments = $subjectIds->isNotEmpty()
            ? SubjectAssignment::whereIn('subject_id', $subjectIds)
                ->with(['subject', 'submissions' => fn($q) => $q->where('user_id', $user->id)])
                ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')
                ->get()
            : collect();

        return view('student.assignments.index', compact('assignments', 'user', 'programCode'));
    }

    /**
     * Display a specific assignment detail for submission.
     */
    public function showAssignment(Request $request, SubjectAssignment $assignment): View
    {
        $user = $request->user();

        // Check if student is explicitly enrolled in the subject offering
        $isEnrolled = $user->enrolledOfferings()->where('subject_id', $assignment->subject_id)->exists();
        if (! $isEnrolled && ! $user->hasRole([\App\Models\Role::ADMIN, \App\Models\Role::SUPER_ADMIN])) {
            abort(403, 'You are not enrolled in the subject offering for this assignment.');
        }

        NewAssignment::clear($user, $assignment->id);

        $assignment->load(['subject', 'submissions' => fn($q) => $q->where('user_id', $user->id)]);
        $submission = $assignment->submissions->first();

        return view('student.assignments.show', compact('assignment', 'submission', 'user'));
    }

    /**
     * Submit / pass an assignment activity (supports file, text, or link).
     */
    public function submitAssignment(Request $request, SubjectAssignment $assignment): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'submission_type' => ['required', 'string', 'in:file,text,link'],
            'file'            => ['nullable', 'file', 'max:51200'], // 50MB max
            'content_text'    => ['nullable', 'string', 'max:50000'],
            'link_url'        => ['nullable', 'url', 'max:2000'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        $type = $validated['submission_type'];

        // Validation per submission type
        if ($type === 'file' && !$request->hasFile('file') && !$assignment->submissions()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['file' => 'Please select a file to upload.']);
        }

        if ($type === 'text' && empty(trim($validated['content_text'] ?? ''))) {
            return back()->withErrors(['content_text' => 'Please enter your written submission content.']);
        }

        if ($type === 'link' && empty(trim($validated['link_url'] ?? ''))) {
            return back()->withErrors(['link_url' => 'Please provide a valid web URL or resource link.']);
        }

        $existing = SubjectSubmission::where('subject_assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        $filePath = $existing?->file_path;
        $fileName = $existing?->file_name;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('submissions', 'public');
        }

        SubjectSubmission::updateOrCreate(
            [
                'subject_assignment_id' => $assignment->id,
                'user_id'               => $user->id,
            ],
            [
                'submission_type' => $type,
                'file_path'       => $filePath,
                'file_name'       => $fileName,
                'link_url'        => $validated['link_url'] ?? $existing?->link_url,
                'content_text'    => $validated['content_text'] ?? $existing?->content_text,
                'notes'           => $validated['notes'] ?? $existing?->notes,
                'status'          => 'submitted',
                'submitted_at'    => now(),
            ]
        );

        return back()->with('status', "Assignment '{$assignment->title}' successfully passed and submitted!");
    }
}
