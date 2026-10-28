<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Announcement;
use App\Models\Role;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectOffering;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin dashboard with institutional counts (users, offerings, classes) and recent activity.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // User Counts
        $totalStudents = User::whereHas('roles', fn($q) => $q->where('name', Role::STUDENT))->count();
        $totalInstructors = User::whereHas('roles', fn($q) => $q->where('name', Role::INSTRUCTOR))->count();
        $totalAdmins = User::whereHas('roles', fn($q) => $q->where('name', Role::ADMIN))->count();
        $totalUsers = User::count();

        // Offerings & Classes Counts
        $totalOfferings = SubjectOffering::count();
        $totalSubjects = Subject::count();
        $totalSections = Section::count();
        $currentTerm = AcademicTerm::where('is_current', true)->first();

        // Calculate total weekly class slots across all offerings
        $allOfferings = SubjectOffering::all();
        $totalClassSlots = 0;
        foreach ($allOfferings as $off) {
            $slots = $off->schedules ?: [];
            $totalClassSlots += count($slots);
        }

        // Recent Activity
        $recentUsers = User::with(['roles', 'section'])
            ->latest()
            ->take(6)
            ->get();

        $recentOfferings = SubjectOffering::with(['subject', 'section', 'academicTerm', 'instructor'])
            ->latest()
            ->take(6)
            ->get();

        $recentAnnouncements = Announcement::with('user')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', [
            'user'                => $user,
            'totalStudents'       => $totalStudents,
            'totalInstructors'    => $totalInstructors,
            'totalAdmins'         => $totalAdmins,
            'totalUsers'          => $totalUsers,
            'totalOfferings'      => $totalOfferings,
            'totalSubjects'       => $totalSubjects,
            'totalSections'       => $totalSections,
            'totalClassSlots'     => $totalClassSlots,
            'currentTerm'         => $currentTerm,
            'recentUsers'         => $recentUsers,
            'recentOfferings'     => $recentOfferings,
            'recentAnnouncements' => $recentAnnouncements,
        ]);
    }
}
