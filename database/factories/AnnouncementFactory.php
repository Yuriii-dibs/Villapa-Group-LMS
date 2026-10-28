<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'content' => fake()->paragraphs(2, true),
            'is_pinned' => false,
        ];
    }
}
