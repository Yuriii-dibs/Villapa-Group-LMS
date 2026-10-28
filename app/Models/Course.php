<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, SoftDeletes;

    public const DIFFICULTY_BEGINNER = 'beginner';
    public const DIFFICULTY_INTERMEDIATE = 'intermediate';
    public const DIFFICULTY_ADVANCED = 'advanced';

    public const APPROVAL_DRAFT = 'draft';
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    public const PUB_DRAFT = 'draft';
    public const PUB_PUBLISHED = 'published';
    public const PUB_ARCHIVED = 'archived';

    protected $fillable = [
        'instructor_id',
        'category_id',
        'title',
        'slug',
        'description',
        'learning_objectives',
        'difficulty',
        'thumbnail_path',
        'approval_status',
        'publication_status',
        'estimated_duration_minutes',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'learning_objectives' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Primary instructor / course author.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Category this course belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Prerequisite courses that must be completed first.
     */
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_prerequisites', 'course_id', 'prerequisite_course_id')
            ->withTimestamps();
    }

    /**
     * Courses that have this course as a prerequisite.
     */
    public function dependentCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_prerequisites', 'prerequisite_course_id', 'course_id')
            ->withTimestamps();
    }

    /**
     * Modules comprising the course syllabus.
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    /**
     * Lessons across all modules.
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }

    /**
     * Student enrollments in this course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Students enrolled in this course.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withPivot(['status', 'enrolled_at', 'completed_at'])
            ->withTimestamps();
    }


    /**
     * Course announcements.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Course review decisions and approval history.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class)->latest('reviewed_at');
    }

    /**
     * Live and scheduled online class sessions.
     */
    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    /**
     * Calculate course progress percentage for a given student.
     */
    public function progressFor(User $user): int
    {
        $enrollment = $this->enrollments()->where('user_id', $user->id)->first();

        return $enrollment ? $enrollment->progressPercentage() : 0;
    }

    public function isPublished(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED
            && $this->publication_status === self::PUB_PUBLISHED;
    }

    public function isPending(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    public function isDraft(): bool
    {
        return $this->approval_status === self::APPROVAL_DRAFT;
    }
}
