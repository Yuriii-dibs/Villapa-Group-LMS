<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatMessageRequest;
use App\Models\Course;
use App\Models\Message;
use App\Models\Role;
use App\Models\SubjectOffering;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Display a portal index showing subject group chat rooms, course rooms, and eligible direct contacts.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Subject Offerings discussion groups (Automatic Membership by enrollment)
        // Admin sees no subject group chats — they are not enrolled in or teaching any offering.
        if ($user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            $offerings = collect();
        } elseif ($user->hasRole(Role::INSTRUCTOR)) {
            $offerings = SubjectOffering::with(['subject', 'section', 'academicTerm', 'instructor'])
                ->where(function ($q) use ($user) {
                    $q->where('instructor_id', $user->id)
                      ->orWhere('instructor_name', 'like', '%' . $user->name . '%');
                })
                ->latest()
                ->get();
        } else {
            // Student: only enrolled offerings appear here.
            $offerings = $user->enrolledOfferings()
                ->with(['subject', 'section', 'academicTerm', 'instructor'])
                ->latest()
                ->get();
        }

        // Legacy course rooms.
        // Students must NOT see all courses they were bulk-enrolled in via course_enrollments —
        // only show courses that directly correspond to a subject offering they are enrolled in.
        if ($user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            $courses = collect(); // Admin has no course channels; direct messages only.
        } elseif ($user->hasRole(Role::INSTRUCTOR)) {
            $courses = $user->coursesAsInstructor()->orderBy('title')->get();
        } else {
            // Student: derive courses only from subject offerings they are enrolled in.
            // Cross-reference offering_enrollments -> subjects -> matching courses by title keywords.
            // This prevents showing courses the student was bulk-enrolled into but never formally scheduled.
            $enrolledOfferingSubjectIds = $user->enrolledOfferings()->pluck('subject_id');
            if ($enrolledOfferingSubjectIds->isNotEmpty()) {
                $subjectTitles = \App\Models\Subject::whereIn('id', $enrolledOfferingSubjectIds)
                    ->pluck('title');
                // Match courses whose title contains the subject title (case-insensitive).
                $courses = Course::with('instructor')
                    ->where(function ($q) use ($subjectTitles) {
                        foreach ($subjectTitles as $title) {
                            $q->orWhere('title', 'like', '%' . $title . '%');
                        }
                    })
                    ->whereHas('enrollments', fn($q) => $q->where('user_id', auth()->id()))
                    ->orderBy('title')
                    ->get();
            } else {
                $courses = collect();
            }
        }

        // 2. Eligible direct message contacts
        $contacts = collect();
        if ($user->hasRole(Role::STUDENT)) {
            // Use offering_enrollments as the source of truth (not legacy course_enrollments)
            // to prevent showing instructors from courses the student was bulk-enrolled into.
            $enrolledOfferingIds = $user->enrolledOfferings()->pluck('subject_offerings.id');

            $offeringInstructorIds = $user->enrolledOfferings()
                ->whereNotNull('instructor_id')
                ->pluck('instructor_id');

            $classmateIds = DB::table('offering_enrollments')
                ->whereIn('subject_offering_id', $enrolledOfferingIds)
                ->where('user_id', '!=', $user->id)
                ->pluck('user_id');

            $allContactIds = $offeringInstructorIds
                ->merge($classmateIds)
                ->unique()
                ->filter();

            $contacts = User::whereIn('id', $allContactIds)->orderBy('name')->get();
        } elseif ($user->hasRole(Role::INSTRUCTOR)) {
            $courseStudentIds = User::whereHas('enrollments.course', fn($q) => $q->where('instructor_id', $user->id))
                ->pluck('id');

            $taughtOfferingIds = SubjectOffering::where('instructor_id', $user->id)
                ->orWhere('instructor_name', 'like', '%' . $user->name . '%')
                ->pluck('id');

            $offeringStudentIds = DB::table('offering_enrollments')
                ->whereIn('subject_offering_id', $taughtOfferingIds)
                ->pluck('user_id');

            $allContactIds = $courseStudentIds
                ->merge($offeringStudentIds)
                ->unique()
                ->filter();

            $contacts = User::whereIn('id', $allContactIds)->orderBy('name')->get();
        } elseif ($user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            // Admin can message all active (non-suspended) users.
            $contacts = User::where('id', '!=', $user->id)
                ->where('status', '!=', User::STATUS_SUSPENDED)
                ->orderBy('name')
                ->get();
        }

        return view('chat.index', compact('offerings', 'courses', 'contacts'));
    }

    /**
     * View or poll subject offering group chat messages.
     */
    public function offeringRoom(Request $request, SubjectOffering $offering): View|JsonResponse
    {
        $user = $request->user();

        // Access check: enrolled student, offering instructor, or Admin
        $canRead = $user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])
            || $user->teachesOffering($offering)
            || $user->isEnrolledInOffering($offering);

        if (! $canRead) {
            abort(403, 'You are not enrolled in or teaching this subject offering and cannot access its group chat.');
        }

        NewMessage::clear($user, 'offering:' . $offering->id);

        $offering->load(['subject', 'section', 'academicTerm', 'instructor', 'students']);

        if ($request->wantsJson() || $request->ajax()) {
            $query = Message::where('subject_offering_id', $offering->id)
                ->whereNull('recipient_id')
                ->with('sender');

            if ($request->filled('after')) {
                $query->where('id', '>', (int) $request->input('after'));
            }

            $messages = $query->orderBy('id', 'asc')->get()->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => e($msg->sender?->name ?? 'User'),
                    'sender_role' => $msg->sender?->getPrimaryRole() ?? 'Student',
                    'body' => e($msg->body),
                    'is_me' => $msg->sender_id === $user->id,
                    'created_at' => $msg->created_at->format('M d, g:i A'),
                ];
            });

            return response()->json(['messages' => $messages]);
        }

        $canSend = $user->teachesOffering($offering)
            || $user->isEnrolledInOffering($offering)
            || $user->hasRole([Role::ADMIN, Role::SUPER_ADMIN]);

        return view('chat.offering', compact('offering', 'canSend'));
    }

    /**
     * Send a message to a subject offering group chat (throttled).
     */
    public function sendOfferingMessage(StoreChatMessageRequest $request, SubjectOffering $offering): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $canSend = $user->teachesOffering($offering)
            || $user->isEnrolledInOffering($offering)
            || $user->hasRole([Role::ADMIN, Role::SUPER_ADMIN]);

        if (! $canSend) {
            abort(403, 'You must be enrolled in or teaching this subject offering to post messages.');
        }

        $message = Message::create([
            'subject_offering_id' => $offering->id,
            'subject_id' => $offering->subject_id,
            'course_id' => null,
            'sender_id' => $user->id,
            'recipient_id' => null,
            'body' => $request->validated('body'),
        ]);

        // Bell notification for everyone else in this subject's group chat
        $offering->loadMissing('subject');
        $roomTitle = 'New message in ' . ($offering->subject->title ?? 'class chat');
        $roomUrl = route('chat.offering', $offering);
        $members = $offering->students()->where('users.id', '!=', $user->id)->get();
        if ($offering->instructor_id && $offering->instructor_id !== $user->id) {
            $instructor = User::find($offering->instructor_id);
            if ($instructor) {
                $members->push($instructor);
            }
        }
        foreach ($members as $member) {
            NewMessage::send($member, 'offering:' . $offering->id, $roomTitle, $user->name . ': ' . $message->body, $roomUrl);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $user->id,
                    'sender_name' => e($user->name),
                    'sender_role' => $user->getPrimaryRole(),
                    'body' => e($message->body),
                    'is_me' => true,
                    'created_at' => $message->created_at->format('M d, g:i A'),
                ],
            ], 201);
        }

        return back()->with('status', 'Message posted to subject group chat.');
    }

    /**
     * View or poll course chat room messages.
     */
    public function courseRoom(Request $request, Course $course): View|JsonResponse
    {
        $user = $request->user();

        // Access check: enrolled student, course instructor, or Admin
        $canRead = $user->hasRole([Role::ADMIN, Role::SUPER_ADMIN])
            || $user->teachesCourse($course)
            || $user->isEnrolledIn($course);

        if (! $canRead) {
            abort(403, 'You are not enrolled in this course and cannot access its chat room.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            $query = Message::where('course_id', $course->id)
                ->whereNull('recipient_id')
                ->with('sender');

            if ($request->filled('after')) {
                $query->where('id', '>', (int) $request->input('after'));
            }

            $messages = $query->orderBy('id', 'asc')->get()->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => e($msg->sender->name),
                    'body' => e($msg->body),
                    'is_me' => $msg->sender_id === $user->id,
                    'created_at' => $msg->created_at->format('M d, g:i A'),
                ];
            });

            return response()->json(['messages' => $messages]);
        }

        $canSend = $user->teachesCourse($course) || $user->isEnrolledIn($course);

        return view('chat.course', compact('course', 'canSend'));
    }

    /**
     * Send a message to a course chat room (throttled).
     */
    public function sendCourseMessage(StoreChatMessageRequest $request, Course $course): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Only enrolled students and the course's instructor can send
        $canSend = $user->teachesCourse($course) || $user->isEnrolledIn($course);
        if (! $canSend) {
            abort(403, 'Only enrolled students and the instructor can post messages.');
        }

        $message = Message::create([
            'course_id' => $course->id,
            'sender_id' => $user->id,
            'recipient_id' => null,
            'body' => $request->validated('body'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $user->id,
                    'sender_name' => e($user->name),
                    'body' => e($message->body),
                    'is_me' => true,
                    'created_at' => $message->created_at->format('M d, g:i A'),
                ],
            ], 201);
        }

        return back()->with('status', 'Message sent.');
    }

    /**
     * View or poll direct messages between student and instructor.
     */
    public function directRoom(Request $request, User $recipient): View|JsonResponse
    {
        $user = $request->user();

        if (! $user->sharesCourseWith($recipient)) {
            abort(403, 'Direct messages are only permitted between a student and an instructor who share a course.');
        }

        NewMessage::clear($user, 'dm:' . $recipient->id);

        if ($request->wantsJson() || $request->ajax()) {
            $query = Message::whereNull('course_id')
                ->where(function ($q) use ($user, $recipient) {
                    $q->where(function ($sq) use ($user, $recipient) {
                        $sq->where('sender_id', $user->id)->where('recipient_id', $recipient->id);
                    })->orWhere(function ($sq) use ($user, $recipient) {
                        $sq->where('sender_id', $recipient->id)->where('recipient_id', $user->id);
                    });
                })
                ->with('sender');

            if ($request->filled('after')) {
                $query->where('id', '>', (int) $request->input('after'));
            }

            $messages = $query->orderBy('id', 'asc')->get()->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => e($msg->sender->name),
                    'body' => e($msg->body),
                    'is_me' => $msg->sender_id === $user->id,
                    'created_at' => $msg->created_at->format('M d, g:i A'),
                ];
            });

            return response()->json(['messages' => $messages]);
        }

        return view('chat.direct', compact('recipient'));
    }

    /**
     * Send a direct message to a shared student or instructor (throttled).
     */
    public function sendDirectMessage(StoreChatMessageRequest $request, User $recipient): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user->sharesCourseWith($recipient)) {
            abort(403, 'Direct messages are only permitted between a student and an instructor who share a course.');
        }

        $message = Message::create([
            'course_id' => null,
            'sender_id' => $user->id,
            'recipient_id' => $recipient->id,
            'body' => $request->validated('body'),
        ]);

        NewMessage::send($recipient, 'dm:' . $user->id, 'New message from ' . $user->name, $message->body, route('chat.direct', $user));

        if ($request->wantsJson()) {
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $user->id,
                    'sender_name' => e($user->name),
                    'body' => e($message->body),
                    'is_me' => true,
                    'created_at' => $message->created_at->format('M d, g:i A'),
                ],
            ], 201);
        }

        return back()->with('status', 'Direct message sent.');
    }
}
