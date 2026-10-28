<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\SubjectOffering;
use App\Models\SubjectSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments across the instructor's subjects.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Subjects taught by this instructor
        $offerings = SubjectOffering::with('subject')
            ->where(function ($q) use ($user) {
                $q->where('instructor_id', $user->id)
                    ->orWhere('instructor_name', 'like', '%' . $user->name . '%');
            })
            ->get();

        $subjectIds = $offerings->pluck('subject_id')->unique();

        $assignments = SubjectAssignment::whereIn('subject_id', $subjectIds)
            ->with(['subject', 'submissions.user'])
            ->withCount([
                'submissions',
                'submissions as pending_count' => fn($q) => $q->whereNull('grade'),
            ])
            ->latest('created_at')
            ->paginate(15);

        return view('instructor.assignments.index', compact('assignments', 'user', 'offerings'));
    }

    /**
     * Show form to create a new assignment.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        $offerings = SubjectOffering::with('subject')
            ->where(function ($q) use ($user) {
                $q->where('instructor_id', $user->id)
                    ->orWhere('instructor_name', 'like', '%' . $user->name . '%');
            })
            ->get();

        $subjects = $offerings->map(fn($o) => $o->subject)->filter()->unique('id')->values();

        // Fallback to all subjects if none linked directly
        if ($subjects->isEmpty()) {
            $subjects = Subject::orderBy('subject_code')->get();
        }

        $selectedSubjectId = $request->input('subject_id');

        return view('instructor.assignments.create', compact('subjects', 'selectedSubjectId'));
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id'   => ['required', 'exists:subjects,id'],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'due_date'     => ['nullable', 'date'],
            'total_points' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $assignment = SubjectAssignment::create($validated);

        return redirect()->route('instructor.assignments.index')
            ->with('status', 'Assignment "' . $assignment->title . '" created successfully.');
    }

    /**
     * Show form to edit an assignment.
     */
    public function edit(SubjectAssignment $assignment): View
    {
        $subjects = Subject::orderBy('subject_code')->get();

        return view('instructor.assignments.edit', compact('assignment', 'subjects'));
    }

    /**
     * Update an assignment.
     */
    public function update(Request $request, SubjectAssignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id'   => ['required', 'exists:subjects,id'],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'due_date'     => ['nullable', 'date'],
            'total_points' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $assignment->update($validated);

        return redirect()->route('instructor.assignments.index')
            ->with('status', 'Assignment "' . $assignment->title . '" updated successfully.');
    }

    /**
     * Delete an assignment.
     */
    public function destroy(SubjectAssignment $assignment): RedirectResponse
    {
        $title = $assignment->title;
        $assignment->delete();

        return redirect()->route('instructor.assignments.index')
            ->with('status', 'Assignment "' . $title . '" deleted.');
    }

    /**
     * Show submissions for a specific assignment.
     */
    public function submissions(SubjectAssignment $assignment): View
    {
        $assignment->load(['subject', 'submissions.user']);

        return view('instructor.assignments.submissions', compact('assignment'));
    }

    /**
     * Grade a student's submission.
     */
    public function grade(Request $request, SubjectSubmission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'grade'    => ['required', 'numeric', 'min:0', 'max:' . ($submission->assignment->total_points ?? 100)],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'grade'    => $validated['grade'],
            'feedback' => $validated['feedback'],
            'status'   => 'graded',
        ]);

        return back()->with('status', 'Grade and feedback recorded for ' . $submission->user->name . '.');
    }
}
