<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseManagementAndLearningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $instructor1;
    protected User $instructor2;
    protected User $student;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::ADMIN);

        $this->instructor1 = User::factory()->create();
        $this->instructor1->assignRole(Role::INSTRUCTOR);

        $this->instructor2 = User::factory()->create();
        $this->instructor2->assignRole(Role::INSTRUCTOR);

        $this->student = User::factory()->create();
        $this->student->assignRole(Role::STUDENT);

        $this->category = Category::factory()->create(['name' => 'Computer Science']);
    }

    public function test_only_published_courses_appear_in_the_catalog(): void
    {
        $published = Course::factory()->create([
            'category_id' => $this->category->id,
            'title' => 'Published Web Development',
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);

        $draft = Course::factory()->create([
            'category_id' => $this->category->id,
            'title' => 'Secret Draft Course',
            'approval_status' => Course::APPROVAL_DRAFT,
            'publication_status' => Course::PUB_DRAFT,
        ]);

        $pending = Course::factory()->create([
            'category_id' => $this->category->id,
            'title' => 'Pending Review Course',
            'approval_status' => Course::APPROVAL_PENDING,
            'publication_status' => Course::PUB_DRAFT,
        ]);

        $response = $this->get('/courses');

        $response->assertStatus(200);
        $response->assertSee('Published Web Development');
        $response->assertDontSee('Secret Draft Course');
        $response->assertDontSee('Pending Review Course');
    }

    public function test_catalog_can_filter_by_category_and_search_by_title(): void
    {
        $businessCategory = Category::factory()->create(['name' => 'Business', 'slug' => 'business']);

        $csCourse = Course::factory()->create([
            'category_id' => $this->category->id,
            'title' => 'Data Structures in PHP',
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);

        $businessCourse = Course::factory()->create([
            'category_id' => $businessCategory->id,
            'title' => 'Financial Modeling 101',
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);

        // Search test
        $searchResponse = $this->get('/courses?search=Data+Structures');
        $searchResponse->assertSee('Data Structures in PHP');
        $searchResponse->assertDontSee('Financial Modeling 101');

        // Category filter test
        $categoryResponse = $this->get('/courses?category=business');
        $categoryResponse->assertSee('Financial Modeling 101');
        $categoryResponse->assertDontSee('Data Structures in PHP');
    }

    public function test_instructor_can_create_draft_course_with_thumbnail_upload(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('thumbnail.jpg', 640, 480);

        $response = $this->actingAs($this->instructor1)->post('/instructor/courses', [
            'category_id' => $this->category->id,
            'title' => 'Intro to Software Engineering',
            'description' => 'Comprehensive primer on modern software development.',
            'difficulty' => 'beginner',
            'estimated_duration_minutes' => 90,
            'learning_objectives' => ['Learn Git', 'Understand Testing'],
            'thumbnail' => $image,
        ]);

        $course = Course::where('title', 'Intro to Software Engineering')->first();
        $this->assertNotNull($course);
        $this->assertEquals($this->instructor1->id, $course->instructor_id);
        $this->assertEquals(Course::APPROVAL_DRAFT, $course->approval_status);
        $this->assertEquals(Course::PUB_DRAFT, $course->publication_status);
        $this->assertNotNull($course->thumbnail_path);

        Storage::disk('public')->assertExists($course->thumbnail_path);

        $response->assertRedirect(route('instructor.courses.edit', $course));
    }

    public function test_instructor_cannot_edit_another_instructors_course(): void
    {
        $course1 = Course::factory()->create([
            'instructor_id' => $this->instructor1->id,
            'category_id' => $this->category->id,
            'title' => 'Instructor 1 Course',
        ]);

        $response = $this->actingAs($this->instructor2)->put("/instructor/courses/{$course1->id}", [
            'category_id' => $this->category->id,
            'title' => 'Malicious Hijack Attempt',
            'description' => 'Tampered text.',
            'difficulty' => 'advanced',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('Instructor 1 Course', $course1->fresh()->title);

        $deleteResponse = $this->actingAs($this->instructor2)->delete("/instructor/courses/{$course1->id}");
        $deleteResponse->assertStatus(403);
        $this->assertFalse($course1->fresh()->trashed());
    }

    public function test_instructor_cannot_publish_directly_and_must_submit_for_approval(): void
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor1->id,
            'category_id' => $this->category->id,
            'approval_status' => Course::APPROVAL_DRAFT,
            'publication_status' => Course::PUB_DRAFT,
        ]);

        $module = Module::factory()->create(['course_id' => $course->id]);
        Lesson::factory()->create(['module_id' => $module->id]);

        // Instructor submits for review
        $response = $this->actingAs($this->instructor1)->post("/instructor/courses/{$course->id}/submit-for-approval");
        $response->assertSessionHasNoErrors();
        $this->assertEquals(Course::APPROVAL_PENDING, $course->fresh()->approval_status);

        // Instructor tries to approve/publish directly
        $publishAttempt = $this->actingAs($this->instructor1)->post("/admin/courses/{$course->id}/approve");
        $publishAttempt->assertStatus(403);
    }

    public function test_admin_can_approve_course_and_it_becomes_published(): void
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor1->id,
            'category_id' => $this->category->id,
            'approval_status' => Course::APPROVAL_PENDING,
            'publication_status' => Course::PUB_DRAFT,
            'published_at' => null,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/courses/{$course->id}/approve");

        $response->assertSessionHasNoErrors();
        $course->refresh();
        $this->assertEquals(Course::APPROVAL_APPROVED, $course->approval_status);
        $this->assertEquals(Course::PUB_PUBLISHED, $course->publication_status);
        $this->assertNotNull($course->published_at);
        $this->assertDatabaseHas('course_reviews', [
            'course_id' => $course->id,
            'reviewer_id' => $this->admin->id,
            'decision' => 'approved',
        ]);
    }

    public function test_admin_can_reject_course_with_required_reason(): void
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor1->id,
            'category_id' => $this->category->id,
            'approval_status' => Course::APPROVAL_PENDING,
            'publication_status' => Course::PUB_DRAFT,
        ]);

        // Missing reason fails
        $failResponse = $this->actingAs($this->admin)->post("/admin/courses/{$course->id}/reject", [
            'reason' => '',
        ]);
        $failResponse->assertSessionHasErrors('reason');

        // Valid reason succeeds
        $successResponse = $this->actingAs($this->admin)->post("/admin/courses/{$course->id}/reject", [
            'reason' => 'Please add more hands-on code examples to lesson 2.',
        ]);
        $successResponse->assertSessionHasNoErrors();

        $course->refresh();
        $this->assertEquals(Course::APPROVAL_REJECTED, $course->approval_status);
        $this->assertDatabaseHas('course_reviews', [
            'course_id' => $course->id,
            'reviewer_id' => $this->admin->id,
            'decision' => 'rejected',
            'reason' => 'Please add more hands-on code examples to lesson 2.',
        ]);
    }

    public function test_admin_cannot_delete_category_that_has_courses(): void
    {
        $category = Category::factory()->create(['name' => 'Protected Category']);
        Course::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_student_can_enroll_in_published_course_and_duplicate_enrollment_is_blocked(): void
    {
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);

        // First enrollment succeeds
        $response = $this->actingAs($this->student)->post("/courses/{$course->slug}/enroll");
        $response->assertRedirect(route('student.courses.show', $course));
        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
        ]);

        // Second enrollment does not duplicate record
        $duplicateResponse = $this->actingAs($this->student)->post("/courses/{$course->slug}/enroll");
        $duplicateResponse->assertRedirect(route('student.courses.show', $course));
        $this->assertCount(1, CourseEnrollment::where('user_id', $this->student->id)->where('course_id', $course->id)->get());
    }

    public function test_student_cannot_read_a_lesson_without_enrolling(): void
    {
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);

        $module = Module::factory()->create(['course_id' => $course->id]);
        $lockedLesson = Lesson::factory()->create(['module_id' => $module->id, 'is_preview' => false]);
        $previewLesson = Lesson::factory()->create(['module_id' => $module->id, 'is_preview' => true]);

        // Locked lesson: student not enrolled -> 403 Forbidden
        $response = $this->actingAs($this->student)->get("/student/courses/{$course->slug}/lessons/{$lockedLesson->slug}");
        $response->assertStatus(403);

        // Preview lesson: student not enrolled -> allowed (200 OK)
        $previewResponse = $this->actingAs($this->student)->get("/student/courses/{$course->slug}/lessons/{$previewLesson->slug}");
        $previewResponse->assertStatus(200);

        // Enroll student -> now locked lesson is accessible
        CourseEnrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now(),
        ]);

        $unlockedResponse = $this->actingAs($this->student)->get("/student/courses/{$course->slug}/lessons/{$lockedLesson->slug}");
        $unlockedResponse->assertStatus(200);
    }

    public function test_progress_percentage_is_correct_and_sets_completed_at_at_100_percent(): void
    {
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);

        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson1 = Lesson::factory()->create(['module_id' => $module->id, 'order' => 1]);
        $lesson2 = Lesson::factory()->create(['module_id' => $module->id, 'order' => 2]);

        $enrollment = CourseEnrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now(),
        ]);

        // Baseline: 0 of 2 lessons = 0%
        $this->assertEquals(0, $enrollment->progressPercentage());
        $this->assertNull($enrollment->completed_at);

        // Complete lesson 1: 1 of 2 lessons = 50%
        $this->actingAs($this->student)->post("/student/lessons/{$lesson1->id}/complete");
        $enrollment->refresh();
        $this->assertEquals(50, $enrollment->progressPercentage());
        $this->assertNull($enrollment->completed_at);

        // Clicking complete twice is safe (idempotent): still 50%
        $this->actingAs($this->student)->post("/student/lessons/{$lesson1->id}/complete");
        $enrollment->refresh();
        $this->assertEquals(50, $enrollment->progressPercentage());
        $this->assertNull($enrollment->completed_at);

        // Complete lesson 2: 2 of 2 lessons = 100% -> sets completed_at and status = completed
        $this->actingAs($this->student)->post("/student/lessons/{$lesson2->id}/complete");
        $enrollment->refresh();
        $this->assertEquals(100, $enrollment->progressPercentage());
        $this->assertNotNull($enrollment->completed_at);
        $this->assertEquals(CourseEnrollment::STATUS_COMPLETED, $enrollment->status);
    }
}
