<?php

namespace App\Models;

use Database\Factories\CourseEnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEnrollment extends Model
{
    /** @use HasFactory<CourseEnrollmentFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DROPPED = 'dropped';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'enrolled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Calculate progress as completed lessons divided by total lessons for this course.
     */
    public function progressPercentage(): int
    {
        $lessonIds = $this->course->lessons()->pluck('lessons.id');
        $totalLessons = $lessonIds->count();

        if ($totalLessons === 0) {
            return 0;
        }

        $completedCount = LessonProgress::where('user_id', $this->user_id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('is_completed', true)
            ->count();

        return (int) round(($completedCount / $totalLessons) * 100);
    }

    /**
     * Evaluate completion: set completed_at and status if progress reaches 100%.
     */
    public function syncCompletion(): void
    {
        $progress = $this->progressPercentage();

        if ($progress >= 100) {
            if (! $this->completed_at) {
                $this->update([
                    'completed_at' => now(),
                    'status' => self::STATUS_COMPLETED,
                ]);
            }
        } else {
            if ($this->completed_at) {
                $this->update([
                    'completed_at' => null,
                    'status' => self::STATUS_ACTIVE,
                ]);
            }
        }
    }
}
