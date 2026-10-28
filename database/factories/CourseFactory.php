<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'instructor_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 99999),
            'description' => fake()->paragraphs(2, true),
            'learning_objectives' => [
                fake()->sentence(),
                fake()->sentence(),
                fake()->sentence(),
            ],
            'difficulty' => Course::DIFFICULTY_BEGINNER,
            'thumbnail_path' => null,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
            'estimated_duration_minutes' => 120,
            'published_at' => now(),
        ];
    }
}
