<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\SubjectOffering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Store an announcement posted by an instructor to one of their subject offerings.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'subject_offering_id' => ['required', 'exists:subject_offerings,id'],
            'title'               => ['required', 'string', 'max:255'],
            'content'             => ['required', 'string'],
        ]);

        $offering = SubjectOffering::findOrFail($validated['subject_offering_id']);

        // Verify authorization
        abort_unless(
            $offering->instructor_id === $user->id ||
            str_contains($offering->instructor_name ?? '', $user->name) ||
            $user->hasRole('admin'),
            403
        );

        Announcement::create([
            'user_id'             => $user->id,
            'title'               => $validated['title'],
            'content'             => $validated['content'],
            'target_type'         => 'offering',
            'subject_offering_id' => $offering->id,
            'subject_id'          => $offering->subject_id,
            'priority'            => 'normal',
        ]);

        return back()->with('status', 'Announcement posted successfully to ' . $offering->subject?->subject_code . '.');
    }
}
