<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'last_login_at',
        'program_id',
        'section_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The degree program (category) this student is enrolled in.
     * Only admins can assign or change this.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'program_id');
    }

    /**
     * Whether this student has been assigned a degree program.
     */
    public function hasProgram(): bool
    {
        return $this->program_id !== null;
    }

    /**
     * Roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    /**
     * Courses authored or taught by this instructor.
     */
    public function coursesAsInstructor(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * Student course enrollments.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Courses the user is enrolled in as a student.
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments')
            ->withPivot(['status', 'enrolled_at', 'completed_at'])
            ->withTimestamps();
    }
    /**
     * Determine whether the user is enrolled in a specific course.
     */
    public function isEnrolledIn(Course|int $course): bool
    {
        $courseId = $course instanceof Course ? $course->id : $course;

        return $this->enrollments()->where('course_id', $courseId)->exists();
    }

    /**
     * Determine whether the user is the instructor of a course.
     */
    public function teachesCourse(Course|int $course): bool
    {
        $courseId = $course instanceof Course ? $course->id : $course;

        return $this->coursesAsInstructor()->where('id', $courseId)->exists();
    }

    /**
     * Direct messages are allowed only between a student and an instructor
     * who share a course (the student is enrolled in the instructor's course).
     */
    public function sharesCourseWith(User $otherUser): bool
    {
        if ($this->id === $otherUser->id) {
            return false;
        }

        if ($this->hasRole([Role::ADMIN, Role::SUPER_ADMIN]) || $otherUser->hasRole([Role::ADMIN, Role::SUPER_ADMIN])) {
            return true;
        }

        // Check if either shares course
        $asStudent = Course::where('instructor_id', $otherUser->id)
            ->whereHas('enrollments', fn($q) => $q->where('user_id', $this->id))
            ->exists();

        if ($asStudent) {
            return true;
        }

        $asInstructor = Course::where('instructor_id', $this->id)
            ->whereHas('enrollments', fn($q) => $q->where('user_id', $otherUser->id))
            ->exists();

        if ($asInstructor) {
            return true;
        }

        // Check Subject Offering connections (instructor <-> student or student <-> student in same offering)
        return SubjectOffering::where(function ($q) use ($otherUser) {
            $q->where('instructor_id', $this->id)
                ->whereHas('students', fn($sq) => $sq->where('users.id', $otherUser->id));
        })->orWhere(function ($q) use ($otherUser) {
            $q->where('instructor_id', $otherUser->id)
                ->whereHas('students', fn($sq) => $sq->where('users.id', $this->id));
        })->orWhere(function ($q) use ($otherUser) {
            $q->whereHas('students', fn($sq) => $sq->where('users.id', $this->id))
                ->whereHas('students', fn($sq) => $sq->where('users.id', $otherUser->id));
        })->exists();
    }

    public function isEnrolledInOffering(SubjectOffering|int $offering): bool
    {
        $id = $offering instanceof SubjectOffering ? $offering->id : $offering;
        return $this->enrolledOfferings()->where('subject_offerings.id', $id)->exists();
    }

    public function teachesOffering(SubjectOffering|int $offering): bool
    {
        $id = $offering instanceof SubjectOffering ? $offering->id : $offering;
        return $this->subjectOfferingsTaught()->where('id', $id)->exists()
            || SubjectOffering::where('id', $id)->where('instructor_name', 'like', '%' . $this->name . '%')->exists();
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subjectOfferingsTaught(): HasMany
    {
        return $this->hasMany(SubjectOffering::class, 'instructor_id');
    }

    public function enrolledOfferings(): BelongsToMany
    {
        return $this->belongsToMany(SubjectOffering::class, 'offering_enrollments')
            ->withPivot(['status', 'enrolled_at'])
            ->withTimestamps();
    }


    /**
     * Lesson progress records.
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * Announcements authored by this user.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Course reviews performed by this reviewer.
     */
    public function reviewsConducted(): HasMany
    {
        return $this->hasMany(CourseReview::class, 'reviewer_id');
    }

    /**
     * Audit log actions recorded for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Determine whether the user has one or any of the given roles.
     *
     * @param string|array<int, string> $roles
     */
    public function hasRole(string|array $roles): bool
    {
        $roleList = is_array($roles) ? $roles : func_get_args();

        return $this->roles->contains(function (Role $role) use ($roleList) {
            return in_array($role->name, $roleList, true);
        });
    }

    /**
     * Assign a role by name or Role instance.
     */
    public function assignRole(string|Role $role): void
    {
        $roleModel = is_string($role) ? Role::where('name', $role)->firstOrFail() : $role;

        if (! $this->roles->contains('id', $roleModel->id)) {
            $this->roles()->attach($roleModel->id);
            $this->load('roles');
        }
    }

    /**
     * Remove a role by name or Role instance.
     */
    public function removeRole(string|Role $role): void
    {
        $roleId = is_string($role) ? Role::where('name', $role)->value('id') : $role->id;

        if ($roleId) {
            $this->roles()->detach($roleId);
            $this->load('roles');
        }
    }

    /**
     * Sync roles by names or IDs.
     *
     * @param array<int, string|int> $roles
     */
    public function syncRoles(array $roles): void
    {
        $roleIds = collect($roles)->map(function ($role) {
            if (is_numeric($role)) {
                return (int) $role;
            }
            return Role::where('name', $role)->value('id');
        })->filter()->all();

        $this->roles()->sync($roleIds);
        $this->load('roles');
    }

    /**
     * Check if user has a permission through any assigned role.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles->flatMap(function (Role $role) {
            return $role->permissions;
        })->contains('name', $permission);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isInstructor(): bool
    {
        return $this->hasRole(Role::INSTRUCTOR);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(Role::STUDENT);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Resolve user's highest priority role for primary redirect.
     */
    public function getPrimaryRole(): string
    {
        $priority = [
            Role::SUPER_ADMIN,
            Role::ADMIN,
            Role::INSTRUCTOR,
            Role::STUDENT,
        ];

        foreach ($priority as $roleName) {
            if ($this->hasRole($roleName)) {
                return $roleName;
            }
        }

        return Role::STUDENT;
    }

    /**
     * Dashboard route name based on primary role.
     */
    public function dashboardRoute(): string
    {
        return match ($this->getPrimaryRole()) {
            Role::SUPER_ADMIN, Role::ADMIN => 'admin.dashboard',
            Role::INSTRUCTOR => 'instructor.dashboard',
            default => 'student.dashboard',
        };
    }

    /**
     * Human-friendly primary role display name.
     */
    public function getPrimaryRoleDisplayName(): string
    {
        $role = $this->getPrimaryRole();
        return match ($role) {
            Role::ADMIN, Role::SUPER_ADMIN => 'School Administrator',
            Role::INSTRUCTOR => 'Instructor',
            Role::STUDENT => 'Student',
            default => ucfirst(str_replace(['-', '_'], ' ', $role)),
        };
    }
}
