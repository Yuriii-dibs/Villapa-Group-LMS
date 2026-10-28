<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'module_id' => Module::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 99999),
            'content_type' => Lesson::TYPE_TEXT,
            'body' => fake()->paragraphs(3, true),
            'video_url' => null,
            'file_path' => null,
            'external_url' => null,
            'duration_minutes' => 15,
            'order' => 1,
            'is_preview' => false,
            'is_published' => true,
        ];
    }
}
