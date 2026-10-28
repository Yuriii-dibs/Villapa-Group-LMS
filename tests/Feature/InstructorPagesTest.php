<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseReview;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstructorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student']);
        Role::firstOrCreate(['name' => 'instructor'], ['display_name' => 'Instructor']);
        Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    }

    public function test_instructor_dashboard_renders_stat_cards_and_empty_state(): void
    {
        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());

        $response = $this->actingAs($instructor)->get('/instructor/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Faculty Dashboard');
        $response->assertSee('My Courses');
        $response->assertSee('Total Students');
        $response->assertSee('Published Courses');
        $response->assertSee('Pending Review');
        $response->assertSee('No student enrollments yet');
        $response->assertSee("authored any courses yet");
    }

    public function test_my_courses_list_shows_status_badges_and_rejection_reason(): void
    {
        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());

        $category = Category::factory()->create(['name' => 'Computer Science']);

        $courseDraft = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'Draft Course',
            'approval_status' => Course::APPROVAL_DRAFT,
            'publication_status' => Course::PUB_DRAFT,
        ]);

        $courseRejected = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'Needs Rework Course',
            'approval_status' => Course::APPROVAL_REJECTED,
            'publication_status' => Course::PUB_DRAFT,
        ]);

        CourseReview::create([
            'course_id' => $courseRejected->id,
            'reviewer_id' => $instructor->id,
            'decision' => CourseReview::DECISION_REJECTED,
            'reason' => 'Please include video lessons for module 2.',
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($instructor)->get('/instructor/courses');

        $response->assertStatus(200);
        $response->assertSee('Draft Course');
        $response->assertSee('Draft');
        $response->assertSee('Needs Rework Course');
        $response->assertSee('Rejected');
        $response->assertSee('Rejection Reason:');
        $response->assertSee('Please include video lessons for module 2.');
        $response->assertSee('Delete');
    }

    public function test_course_create_form_handles_draft_and_submit_buttons(): void
    {
        Storage::fake('public');

        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());
        $category = Category::factory()->create();

        $file = UploadedFile::fake()->image('thumbnail.jpg');

        $response = $this->actingAs($instructor)->post('/instructor/courses', [
            'title' => 'Complete Python Bootcamp',
            'category_id' => $category->id,
            'difficulty' => 'beginner',
            'description' => 'Comprehensive introduction to Python programming.',
            'estimated_duration_minutes' => 120,
            'thumbnail' => $file,
            'action' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'title' => 'Complete Python Bootcamp',
            'instructor_id' => $instructor->id,
            'approval_status' => Course::APPROVAL_DRAFT,
        ]);

        $course = Course::where('title', 'Complete Python Bootcamp')->first();
        $this->assertNotNull($course->thumbnail_path);
        Storage::disk('public')->assertExists($course->thumbnail_path);
    }

    public function test_course_edit_form_submits_for_approval_when_lessons_exist(): void
    {
        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());
        $category = Category::factory()->create();

        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'Pending Approval Course',
            'approval_status' => Course::APPROVAL_DRAFT,
        ]);

        $module = Module::factory()->create(['course_id' => $course->id]);
        Lesson::factory()->create(['module_id' => $module->id]);

        $response = $this->actingAs($instructor)->put("/instructor/courses/{$course->id}", [
            'title' => 'Pending Approval Course (Updated)',
            'category_id' => $category->id,
            'difficulty' => 'intermediate',
            'description' => 'Updated curriculum description with lessons ready.',
            'action' => 'submit',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Pending Approval Course (Updated)',
            'approval_status' => Course::APPROVAL_PENDING,
        ]);
    }

    public function test_lesson_management_inside_course_moves_up_and_down(): void
    {
        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());

        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'approval_status' => Course::APPROVAL_DRAFT,
        ]);

        $module = Module::factory()->create(['course_id' => $course->id]);

        $lessonA = Lesson::factory()->create([
            'module_id' => $module->id,
            'title' => 'Lesson Alpha',
            'order' => 1,
        ]);

        $lessonB = Lesson::factory()->create([
            'module_id' => $module->id,
            'title' => 'Lesson Beta',
            'order' => 2,
        ]);

        // Move Lesson B up
        $response = $this->actingAs($instructor)->post("/instructor/lessons/{$lessonB->id}/move-up");
        $response->assertRedirect();

        $lessonA->refresh();
        $lessonB->refresh();

        $this->assertEquals(1, $lessonB->order);
        $this->assertEquals(2, $lessonA->order);

        // Move Lesson B back down
        $response = $this->actingAs($instructor)->post("/instructor/lessons/{$lessonB->id}/move-down");
        $response->assertRedirect();

        $lessonA->refresh();
        $lessonB->refresh();

        $this->assertEquals(1, $lessonA->order);
        $this->assertEquals(2, $lessonB->order);
    }

    public function test_students_list_displays_enrolled_students_with_progress(): void
    {
        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());

        $student = User::factory()->create(['name' => 'Maria Santos', 'email' => 'maria@example.com', 'status' => User::STATUS_ACTIVE]);
        $student->roles()->attach(Role::where('name', 'student')->first());

        $category = Category::factory()->create(['name' => 'Design']);
        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'UI/UX Fundamentals',
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);

        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson1 = Lesson::factory()->create(['module_id' => $module->id, 'order' => 1]);
        $lesson2 = Lesson::factory()->create(['module_id' => $module->id, 'order' => 2]);

        CourseEnrollment::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        // Complete 1 of 2 lessons (50% progress)
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson1->id,
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($instructor)->get('/instructor/students');

        $response->assertStatus(200);
        $response->assertSee('Enrolled Students');
        $response->assertSee('Maria Santos');
        $response->assertSee('UI/UX Fundamentals');
        $response->assertSee('50%');
        $response->assertSee('In Progress');
        $response->assertSee('Message');
    }
}
