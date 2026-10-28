<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\Section;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of institutional users with search, role filter, and pagination.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles', 'program', 'section')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $roleName = $request->input('role');
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users    = $query->paginate(15)->withQueryString();
        $roles    = Role::whereIn('name', [Role::STUDENT, Role::INSTRUCTOR, Role::ADMIN])->get();
        $programs = Category::orderBy('name')->get();
        $sections = Section::orderBy('name')->get();

        return view('admin.users.index', [
            'users'         => $users,
            'roles'         => $roles,
            'programs'      => $programs,
            'sections'      => $sections,
            'currentSearch' => $request->input('search', ''),
            'currentRole'   => $request->input('role', ''),
            'currentStatus' => $request->input('status', ''),
        ]);
    }

    /**
     * Create a new user (Student, Instructor, or Admin).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::min(8)],
            'role'       => ['required', 'string', 'in:student,instructor,admin'],
            'program_id' => ['nullable', 'exists:categories,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'status'     => User::STATUS_ACTIVE,
            'program_id' => $validated['program_id'] ?: null,
            'section_id' => $validated['section_id'] ?: null,
        ]);

        $user->assignRole($validated['role']);

        return back()->with('status', ucfirst($validated['role']) . " account for {$user->name} created successfully.");
    }

    /**
     * Edit a user profile and institutional assignments.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'       => ['nullable', 'string', 'in:student,instructor,admin'],
            'program_id' => ['nullable', 'exists:categories,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
        ]);

        $user->update([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'program_id' => $validated['program_id'] ?: null,
            'section_id' => $validated['section_id'] ?: null,
        ]);

        if (!empty($validated['role']) && $user->id !== auth()->id()) {
            $user->syncRoles([$validated['role']]);
        }

        return back()->with('status', "User details for {$user->name} updated successfully.");
    }

    /**
     * Delete a student or instructor account permanently.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own administrator account.');
        }

        if ($user->hasRole(Role::ADMIN)) {
            return back()->with('error', 'Administrator accounts cannot be deleted directly.');
        }

        $isStudent = $user->isStudent();
        $isInstructor = $user->hasRole(Role::INSTRUCTOR);

        if (! $isStudent && ! $isInstructor) {
            return back()->with('error', 'Only student and instructor accounts can be deleted.');
        }

        $userName = $user->name;

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $isInstructor) {
            // Clean up student-specific records
            \Illuminate\Support\Facades\DB::table('offering_enrollments')->where('user_id', $user->id)->delete();
            \Illuminate\Support\Facades\DB::table('course_enrollments')->where('user_id', $user->id)->delete();
            \Illuminate\Support\Facades\DB::table('subject_submissions')->where('user_id', $user->id)->delete();
            \Illuminate\Support\Facades\DB::table('lesson_progress')->where('user_id', $user->id)->delete();

            // Clean up instructor-specific records
            if ($isInstructor) {
                // Preserve instructor name on offerings while releasing the foreign key
                \Illuminate\Support\Facades\DB::table('subject_offerings')
                    ->where('instructor_id', $user->id)
                    ->update([
                        'instructor_name' => $user->name,
                        'instructor_id'   => null,
                    ]);

                // Reassign legacy courses to avoid restrictOnDelete constraint
                \Illuminate\Support\Facades\DB::table('courses')
                    ->where('instructor_id', $user->id)
                    ->update(['instructor_id' => auth()->id()]);

                // Clean up reviews
                \Illuminate\Support\Facades\DB::table('course_reviews')
                    ->where('reviewer_id', $user->id)
                    ->delete();
            }

            // Clean up messages
            \Illuminate\Support\Facades\DB::table('messages')
                ->where('sender_id', $user->id)
                ->orWhere('recipient_id', $user->id)
                ->delete();

            $user->roles()->detach();
            $user->delete();
        });

        $roleLabel = $isInstructor ? 'Instructor' : 'Student';
        return back()->with('status', "{$roleLabel} account for {$userName} has been permanently deleted.");
    }

    /**
     * Reset user password by administrator.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('status', "Password for {$user->name} has been reset successfully.");
    }

    /**
     * Create a new student account directly from the admin panel.
     */
    public function storeStudent(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::min(8)],
            'program_id' => ['nullable', 'exists:categories,id'],
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'status'     => User::STATUS_ACTIVE,
            'program_id' => $validated['program_id'] ?: null,
        ]);

        $user->assignRole(Role::STUDENT);

        return back()->with('status', "Student account for {$user->name} created successfully.");
    }

    /**
     * Update the assigned role for a user.
     * An admin cannot change their own role.
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $newRole = $request->validated('role');
        $user->syncRoles([$newRole]);

        return back()->with('status', "User {$user->name} assigned to the " . ucfirst($newRole) . ' role.');
    }

    /**
     * Update the account status (active, suspended, pending) for a user.
     * An admin cannot suspend themselves.
     */
    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $newStatus = $request->validated('status');
        $user->status = $newStatus;
        $user->save();

        return back()->with('status', "Account status for {$user->name} set to " . ucfirst($newStatus) . '.');
    }

    /**
     * Assign or change a student's degree program (category).
     * Only admins can perform this action.
     */
    public function updateProgram(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'program_id' => ['nullable', 'exists:categories,id'],
        ]);

        $user->program_id = $validated['program_id'] ?: null;
        $user->save();

        $programName = $user->program_id
            ? Category::find($user->program_id)?->name ?? 'Unknown'
            : 'None';

        return back()->with('status', "Program for {$user->name} set to: {$programName}.");
    }

    /**
     * Approve a pending instructor and activate their account.
     */
    public function approveInstructor(Request $request, User $user): RedirectResponse
    {
        $this->authorize('approveInstructor', $user);

        $user->status = User::STATUS_ACTIVE;
        if (! $user->hasRole(Role::INSTRUCTOR)) {
            $user->assignRole(Role::INSTRUCTOR);
        }
        $user->save();

        return back()->with('status', "Instructor {$user->name} has been approved and activated.");
    }

    /**
     * Approve a pending student account and activate it.
     * Optionally assigns a degree program at the same time.
     */
    public function approveStudent(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        abort_if(! $user->isPending(), 422, 'Account is not pending.');

        $validated = $request->validate([
            'program_id' => ['nullable', 'exists:categories,id'],
        ]);

        $user->status     = User::STATUS_ACTIVE;
        $user->program_id = $validated['program_id'] ?: null;
        $user->save();

        return back()->with('status', "Student account for {$user->name} has been approved and activated.");
    }
}
