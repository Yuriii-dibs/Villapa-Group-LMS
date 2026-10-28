<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Section;
use App\Models\SubjectOffering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of institutional announcements with audience filtering.
     */
    public function index(Request $request): View
    {
        $targetType = $request->input('target_type');

        $query = Announcement::with(['user', 'section', 'subjectOffering.subject', 'subjectOffering.section'])
            ->latest();

        if ($targetType) {
            $query->where('target_type', $targetType);
        }

        $announcements = $query->paginate(15)->withQueryString();
        $offerings = SubjectOffering::with(['subject', 'section'])->get();
        $sections  = Section::orderBy('name')->get();

        return view('admin.announcements.index', compact('announcements', 'offerings', 'sections', 'targetType'));
    }

    /**
     * Store a new announcement (Global, by Section, or by Offering).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'content'             => ['required', 'string'],
            'target_type'         => ['required', 'in:global,section,offering'],
            'section_id'          => ['nullable', 'exists:sections,id'],
            'subject_offering_id' => ['nullable', 'exists:subject_offerings,id'],
            'priority'            => ['nullable', 'string', 'in:low,normal,urgent'],
        ]);

        $subjectId = null;
        if (!empty($validated['subject_offering_id'])) {
            $off = SubjectOffering::find($validated['subject_offering_id']);
            $subjectId = $off?->subject_id;
        }

        Announcement::create([
            'user_id'             => $request->user()->id,
            'title'               => $validated['title'],
            'content'             => $validated['content'],
            'target_type'         => $validated['target_type'],
            'section_id'          => $validated['target_type'] === 'section' ? ($validated['section_id'] ?? null) : null,
            'subject_offering_id' => $validated['target_type'] === 'offering' ? ($validated['subject_offering_id'] ?? null) : null,
            'subject_id'          => $subjectId,
            'priority'            => $validated['priority'] ?? 'normal',
        ]);

        return back()->with('status', 'Announcement published successfully.');
    }

    /**
     * Delete an announcement.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcement->delete();

        return back()->with('status', "Announcement '{$title}' removed.");
    }
}
