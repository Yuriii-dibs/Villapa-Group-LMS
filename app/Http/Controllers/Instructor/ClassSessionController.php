<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassSessionRequest;
use App\Http\Requests\UpdateClassSessionRequest;
use App\Models\ClassSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClassSessionController extends Controller
{
    /**
     * Display all online class sessions across the instructor's courses.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $sessions = ClassSession::whereHas('course', function ($q) use ($user) {
            $q->where('instructor_id', $user->id);
        })
        ->with(['course', 'attendances'])
        ->withCount('attendances')
        ->orderBy('starts_at', 'desc')
        ->paginate(15);

        return view('instructor.classes.index', compact('sessions'));
    }

    /**
     * Show form to schedule a new online class session.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $courses = $user->coursesAsInstructor()->orderBy('title')->get();

        return view('instructor.classes.create', compact('courses'));
    }

    /**
     * Store a newly created class session.
     */
    public function store(StoreClassSessionRequest $request): RedirectResponse
    {
        $session = ClassSession::create([
            'course_id' => (int) $request->validated('course_id'),
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
            'provider' => $request->validated('provider'),
            'meeting_url' => $request->validated('meeting_url'),
            'room_code' => Str::random(16),
            'status' => ClassSession::STATUS_SCHEDULED,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('instructor.classes.index')
            ->with('status', "Online class session '{$session->title}' scheduled successfully.");
    }

    /**
     * Show form to edit an existing class session.
     */
    public function edit(ClassSession $session): View
    {
        $this->authorize('manage', $session);

        return view('instructor.classes.edit', compact('session'));
    }

    /**
     * Update an existing class session.
     */
    public function update(UpdateClassSessionRequest $request, ClassSession $session): RedirectResponse
    {
        $session->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
            'provider' => $request->validated('provider'),
            'meeting_url' => $request->validated('meeting_url'),
        ]);

        return redirect()->route('instructor.classes.index')
            ->with('status', "Session '{$session->title}' has been updated.");
    }

    /**
     * Cancel and delete a class session.
     */
    public function destroy(ClassSession $session): RedirectResponse
    {
        $this->authorize('manage', $session);

        $session->delete();

        return back()->with('status', 'Class session was canceled and deleted.');
    }

    /**
     * Start the live class session (status: live).
     */
    public function start(Request $request, ClassSession $session): RedirectResponse
    {
        $this->authorize('manage', $session);

        $session->status = ClassSession::STATUS_LIVE;
        $session->save();

        return redirect()->route('classes.show', $session)
            ->with('status', 'Class session is now LIVE. Students can join.');
    }

    /**
     * End the live class session (status: ended).
     */
    public function end(Request $request, ClassSession $session): RedirectResponse
    {
        $this->authorize('manage', $session);

        $session->status = ClassSession::STATUS_ENDED;
        $session->save();

        return redirect()->route('instructor.classes.index')
            ->with('status', "Class session '{$session->title}' has ended.");
    }

    /**
     * View the attendance log for a session.
     */
    public function attendance(ClassSession $session): View
    {
        $this->authorize('manage', $session);

        $attendances = $session->attendances()
            ->with('user')
            ->orderBy('joined_at', 'asc')
            ->get();

        return view('instructor.classes.attendance', compact('session', 'attendances'));
    }
}
