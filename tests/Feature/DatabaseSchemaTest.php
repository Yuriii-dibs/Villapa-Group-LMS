<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseReview;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_factories_create_valid_records(): void
    {
        $category = Category::factory()->create();
        $course = Course::factory()->create(['category_id' => $category->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id]);
        $enrollment = CourseEnrollment::factory()->create(['course_id' => $course->id]);
        $announcement = Announcement::factory()->create(['course_id' => $course->id]);
        $review = CourseReview::factory()->create(['course_id' => $course->id]);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
        $this->assertDatabaseHas('modules', ['id' => $module->id]);
        $this->assertDatabaseHas('lessons', ['id' => $lesson->id]);
        $this->assertDatabaseHas('course_enrollments', ['id' => $enrollment->id]);
        $this->assertDatabaseHas('announcements', ['id' => $announcement->id]);
        $this->assertDatabaseHas('course_reviews', ['id' => $review->id]);
    }

    public function test_course_and_module_relationships(): void
    {
        $category = Category::factory()->create();
        $instructor = User::factory()->create();
        $course = Course::factory()->create([
            'category_id' => $category->id,
            'instructor_id' => $instructor->id,
        ]);

        $module1 = Module::factory()->create(['course_id' => $course->id, 'order' => 1]);
        $module2 = Module::factory()->create(['course_id' => $course->id, 'order' => 2]);
        $lesson1 = Lesson::factory()->create(['module_id' => $module1->id, 'order' => 1]);
        $lesson2 = Lesson::factory()->create(['module_id' => $module1->id, 'order' => 2]);
        $lesson3 = Lesson::factory()->create(['module_id' => $module2->id, 'order' => 1]);

        $this->assertCount(2, $course->modules);
        $this->assertCount(3, $course->lessons);
        $this->assertEquals($category->id, $course->category->id);
        $this->assertEquals($instructor->id, $course->instructor->id);
        $this->assertCount(1, $category->courses);
        $this->assertCount(1, $instructor->coursesAsInstructor);
    }

    public function test_course_prerequisites_relationship(): void
    {
        $courseA = Course::factory()->create(['title' => 'Calculus I']);
        $courseB = Course::factory()->create(['title' => 'Calculus II']);

        $courseB->prerequisites()->attach($courseA->id);

        $this->assertTrue($courseB->prerequisites->contains($courseA));
        $this->assertTrue($courseA->dependentCourses->contains($courseB));
    }

    public function test_duplicate_course_enrollment_fails(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        CourseEnrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        CourseEnrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now(),
        ]);
    }

    public function test_category_with_courses_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Course::factory()->create(['category_id' => $category->id]);

        $this->expectException(QueryException::class);
        $category->delete();
    }

    public function test_cascade_delete_course_removes_modules_and_lessons(): void
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id]);

        // Force delete bypasses SoftDeletes to trigger DB foreign key cascades
        $course->forceDelete();

        $this->assertDatabaseMissing('modules', ['id' => $module->id]);
        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
    }

}

