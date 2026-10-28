<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAndStudentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student']);
        Role::firstOrCreate(['name' => 'instructor'], ['display_name' => 'Instructor']);
        Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    }

    public function test_landing_page_renders_headline_cta_buttons_and_featured_courses(): void
    {
        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());

        $category = Category::factory()->create(['name' => 'Web Development', 'slug' => 'web-dev']);

        for ($i = 1; $i <= 4; $i++) {
            Course::factory()->create([
                'instructor_id' => $instructor->id,
                'category_id' => $category->id,
                'title' => "Featured Course {$i}",
                'slug' => "featured-course-{$i}",
                'approval_status' => Course::APPROVAL_APPROVED,
                'publication_status' => Course::PUB_PUBLISHED,
                'published_at' => now()->subDays($i),
            ]);
        }

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Learn with purpose.');
        $response->assertSee('Grow with confidence.');
        $response->assertSee('Explore Courses');
        $response->assertSee('Sign In');
        $response->assertSee('Featured Course 1');
        $response->assertSee('Featured Course 2');
        $response->assertSee('Featured Course 3');
    }

    public function test_split_screen_login_and_auth_views_render(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign in to your account');
        $response->assertSee('Learn with purpose.');
        $response->assertSee('x-data="{ show: false }"', false);

        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create Student Account');
        $response->assertSee('Register as Student');

        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Reset Password');
        $response->assertSee('Email Password Reset Link');
    }

    public function test_course_show_page_renders_syllabus_and_enroll_action(): void
    {
        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());
        $category = Category::factory()->create();

        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'Mastering Laravel 12',
            'slug' => 'mastering-laravel-12',
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
            'published_at' => now(),
        ]);

        $module = Module::factory()->create(['course_id' => $course->id, 'title' => 'Basics']);
        Lesson::factory()->create(['module_id' => $module->id, 'title' => 'Introduction to Laravel']);

        $response = $this->get('/courses/mastering-laravel-12');

        $response->assertStatus(200);
        $response->assertSee('Mastering Laravel 12');
        $response->assertSee('Course Syllabus');
        $response->assertSee('Introduction to Laravel');
        $response->assertSee('Sign In to Enroll');
    }

    public function test_custom_error_pages_render(): void
    {
        // 404 Not Found
        $response = $this->get('/courses/non-existent-course-slug');
        $response->assertStatus(404);
        $response->assertSee('Page Not Found');
        $response->assertSee('Browse Courses');

        // 403 Forbidden: non-enrolled student opening another course lesson
        $student = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $student->roles()->attach(Role::where('name', 'student')->first());

        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());
        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
            'published_at' => now(),
        ]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id, 'is_preview' => false]);

        $response = $this->actingAs($student)->get("/student/courses/{$course->slug}/lessons/{$lesson->slug}");
        $response->assertStatus(403);
        $response->assertSee('Access Denied');
    }

    public function test_student_dashboard_empty_state_has_explore_courses_button(): void
    {
        $student = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $student->roles()->attach(Role::where('name', 'student')->first());

        $response = $this->actingAs($student)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Student Learning Center');
        $response->assertSee('Explore Courses');
        $response->assertSee('You are not enrolled in any courses yet');
    }

    public function test_student_lesson_reader_has_previous_next_mark_as_complete_and_progress_bar(): void
    {
        $student = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $student->roles()->attach(Role::where('name', 'student')->first());

        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->roles()->attach(Role::where('name', 'instructor')->first());

        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
            'published_at' => now(),
        ]);

        $module = Module::factory()->create(['course_id' => $course->id]);
        $lesson1 = Lesson::factory()->create(['module_id' => $module->id, 'title' => 'First Lesson', 'order' => 1]);
        $lesson2 = Lesson::factory()->create(['module_id' => $module->id, 'title' => 'Second Lesson', 'order' => 2]);
        $lesson3 = Lesson::factory()->create(['module_id' => $module->id, 'title' => 'Third Lesson', 'order' => 3]);

        CourseEnrollment::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Visit lesson 2
        $response = $this->actingAs($student)->get("/student/courses/{$course->slug}/lessons/{$lesson2->slug}");

        $response->assertStatus(200);
        $response->assertSee('Course Completion');
        $response->assertSee('Mark as Complete');
        $response->assertSee('Previous Lesson');
        $response->assertSee('Next Lesson');
        $response->assertSee($lesson1->slug);
        $response->assertSee($lesson3->slug);
    }

    public function test_authenticated_user_sees_app_layout_on_catalog_without_public_hero(): void
    {
        $student = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $student->roles()->attach(Role::where('name', 'student')->first());

        $response = $this->actingAs($student)->get('/courses');

        $response->assertStatus(200);
        $response->assertSee('Course Catalog');
        // App layout navigation present
        $response->assertSee('Dashboard');
        $response->assertSee('Messages');
        $response->assertSee('Calendar');
        // Guest hero and public navbar NOT present
        $response->assertDontSee('Learn with purpose.<br>Grow with confidence.', false);
        $response->assertDontSee('Structured courses, live online classes, and direct conversations with your instructors.');
    }
}
