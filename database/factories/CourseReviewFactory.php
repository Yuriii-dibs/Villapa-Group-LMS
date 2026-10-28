<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseReview>
 */
class CourseReviewFactory extends Factory
{
    protected $model = CourseReview::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'reviewer_id' => User::factory(),
            'decision' => CourseReview::DECISION_APPROVED,
            'reason' => 'All syllabus criteria and learning objectives met institutional guidelines.',
            'reviewed_at' => now(),
        ];
    }
}
