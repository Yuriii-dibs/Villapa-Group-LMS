<?php

namespace App\Models;

use Database\Factories\CourseReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseReview extends Model
{
    /** @use HasFactory<CourseReviewFactory> */
    use HasFactory;

    public const DECISION_APPROVED = 'approved';
    public const DECISION_REJECTED = 'rejected';
    public const DECISION_REVISION_REQUESTED = 'revision_requested';

    protected $fillable = [
        'course_id',
        'reviewer_id',
        'decision',
        'reason',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
