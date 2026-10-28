<?php

namespace App\Http\Controllers;

use App\Models\ClassAttendance;
use App\Models\ClassSession;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassSessionController extends Controller
{
    /**
     * Join/View an online class session.
     * Enforces enrollment, 15-minute pre-start window, and single-record attendance.
     */
    public function show(Request $request, ClassSession $session): View
    {
        $user = $request->user();

        // 1. Authorization check: must be instructor, enrolled student, or Admin
        $isInstructor = $user->teachesCourse($session->course);
        $isEnrolledStudent = $user->isEnrolledIn($session->course);
        $isAdmin = $user->hasRole([Role::ADMIN, Role::SUPER_ADMIN]);

        if (! $isInstructor && ! $isEnrolledStudent && ! $isAdmin) {
            abort(403, 'You are not enrolled in this course and cannot join this online class.');
        }

        // 2. Window restriction check for students
        if ($isEnrolledStudent && ! $isInstructor && ! $isAdmin) {
            if ($session->isEnded()) {
                abort(403, 'This online class session has already ended.');
            }

            if (! $session->canStudentJoin()) {
                abort(403, 'Students can only join when the session is live or within 15 minutes before it starts.');
            }
        }

        // 3. Record attendance once when opening the join page during live or active window
        if (! $isAdmin) {
            ClassAttendance::firstOrCreate(
                [
                    'session_id' => $session->id,
                    'user_id' => $user->id,
                ],
                [
                    'joined_at' => now(),
                ]
            );
        }

        return view('classes.show', [
            'session' => $session,
            'isInstructor' => $isInstructor,
            'user' => $user,
        ]);
    }
}
