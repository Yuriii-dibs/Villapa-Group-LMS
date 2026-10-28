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
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminToolsAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $instructor;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin.test@school.test',
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->admin->assignRole(Role::ADMIN);

        $this->instructor = User::factory()->create([
            'email' => 'instructor.test@school.test',
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->instructor->assignRole(Role::INSTRUCTOR);

        $this->student = User::factory()->create([
            'email' => 'student.test@school.test',
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->student->assignRole(Role::STUDENT);
    }

    public function test_admin_can_view_user_list_with_search_and_role_filters(): void
    {
        // Additional users
        $targetUser = User::factory()->create([
            'name' => 'Alice Wonder',
            'email' => 'alice@school.test',
            'status' => User::STATUS_ACTIVE,
        ]);
        $targetUser->assignRole(Role::STUDENT);

        $otherUser = User::factory()->create([
            'name' => 'Bob Builder',
            'email' => 'bob@school.test',
            'status' => User::STATUS_PENDING,
        ]);
        $otherUser->assignRole(Role::INSTRUCTOR);

        // Regular student cannot access
        $this->actingAs($this->student)->get('/admin/users')->assertStatus(403);

        // Admin can view all
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Alice Wonder');
        $response->assertSee('Bob Builder');

        // Search filter
        $searchResponse = $this->actingAs($this->admin)->get('/admin/users?search=Alice');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Alice Wonder');
        $searchResponse->assertDontSee('Bob Builder');

        // Role filter
        $roleResponse = $this->actingAs($this->admin)->get('/admin/users?role=' . Role::INSTRUCTOR);
        $roleResponse->assertStatus(200);
        $roleResponse->assertSee('Bob Builder');
        $roleResponse->assertDontSee('Alice Wonder');

        // Status filter
        $statusResponse = $this->actingAs($this->admin)->get('/admin/users?status=' . User::STATUS_PENDING);
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('Bob Builder');
        $statusResponse->assertDontSee('Alice Wonder');
    }

    public function test_admin_can_change_another_users_role_but_cannot_change_their_own(): void
    {
        $targetStudent = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $targetStudent->assignRole(Role::STUDENT);

        // Admin changes student to instructor
        $response = $this->actingAs($this->admin)
            ->post("/admin/users/{$targetStudent->id}/role", [
                'role' => Role::INSTRUCTOR,
            ]);

        $response->assertRedirect();
        $this->assertTrue($targetStudent->fresh()->hasRole(Role::INSTRUCTOR));
        $this->assertFalse($targetStudent->fresh()->hasRole(Role::STUDENT));

        // Admin attempts to change their own role -> 403 Forbidden
        $selfChangeResponse = $this->actingAs($this->admin)
            ->post("/admin/users/{$this->admin->id}/role", [
                'role' => Role::STUDENT,
            ]);

        $selfChangeResponse->assertStatus(403);
        $this->assertTrue($this->admin->fresh()->hasRole(Role::ADMIN));
    }

    public function test_admin_can_suspend_or_activate_another_user_but_cannot_suspend_themselves(): void
    {
        $targetUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $targetUser->assignRole(Role::STUDENT);

        // Suspend user
        $suspendResponse = $this->actingAs($this->admin)
            ->post("/admin/users/{$targetUser->id}/status", [
                'status' => User::STATUS_SUSPENDED,
            ]);

        $suspendResponse->assertRedirect();
        $this->assertEquals(User::STATUS_SUSPENDED, $targetUser->fresh()->status);

        // Activate user
        $activateResponse = $this->actingAs($this->admin)
            ->post("/admin/users/{$targetUser->id}/status", [
                'status' => User::STATUS_ACTIVE,
            ]);

        $activateResponse->assertRedirect();
        $this->assertEquals(User::STATUS_ACTIVE, $targetUser->fresh()->status);

        // Self-suspension attempt -> 403 Forbidden
        $selfSuspendResponse = $this->actingAs($this->admin)
            ->post("/admin/users/{$this->admin->id}/status", [
                'status' => User::STATUS_SUSPENDED,
            ]);

        $selfSuspendResponse->assertStatus(403);
        $this->assertEquals(User::STATUS_ACTIVE, $this->admin->fresh()->status);
    }

    public function test_admin_can_approve_pending_instructor(): void
    {
        $pendingInstructor = User::factory()->create([
            'status' => User::STATUS_PENDING,
        ]);
        $pendingInstructor->assignRole(Role::INSTRUCTOR);

        $response = $this->actingAs($this->admin)
            ->post("/admin/users/{$pendingInstructor->id}/approve-instructor");

        $response->assertRedirect();
        $this->assertEquals(User::STATUS_ACTIVE, $pendingInstructor->fresh()->status);
        $this->assertTrue($pendingInstructor->fresh()->hasRole(Role::INSTRUCTOR));
    }

    public function test_student_dashboard_calculates_exact_metrics_and_next_lesson(): void
    {
        $category = Category::factory()->create();

        // Course 1: 2 lessons. Student completes 1 lesson (50% progress)
        $course1 = Course::factory()->create([
            'category_id' => $category->id,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);
        $mod1 = Module::factory()->create(['course_id' => $course1->id, 'order' => 1]);
        $lesson1_1 = Lesson::factory()->create(['module_id' => $mod1->id, 'order' => 1, 'title' => 'Intro Lesson 1']);
        $lesson1_2 = Lesson::factory()->create(['module_id' => $mod1->id, 'order' => 2, 'title' => 'Advanced Lesson 2']);

        // Course 2: 2 lessons. Student completes 2 lessons (100% progress)
        $course2 = Course::factory()->create([
            'category_id' => $category->id,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);
        $mod2 = Module::factory()->create(['course_id' => $course2->id, 'order' => 1]);
        $lesson2_1 = Lesson::factory()->create(['module_id' => $mod2->id, 'order' => 1, 'title' => 'Course 2 Lesson 1']);
        $lesson2_2 = Lesson::factory()->create(['module_id' => $mod2->id, 'order' => 2, 'title' => 'Course 2 Lesson 2']);

        // Enrollments
        $enrollment1 = CourseEnrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $course1->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now()->subDay(),
        ]);
        $enrollment2 = CourseEnrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $course2->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now()->subDays(2),
            'completed_at' => now()->subHours(5),
        ]);

        // Mark progress: Course 1 lesson 1 complete
        LessonProgress::create([
            'user_id' => $this->student->id,
            'lesson_id' => $lesson1_1->id,
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        // Course 2 lessons both complete
        LessonProgress::create([
            'user_id' => $this->student->id,
            'lesson_id' => $lesson2_1->id,
            'is_completed' => true,
            'completed_at' => now(),
        ]);
        LessonProgress::create([
            'user_id' => $this->student->id,
            'lesson_id' => $lesson2_2->id,
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->student)->get('/student/dashboard');
        $response->assertStatus(200);

        // Assert exact view data
        $response->assertViewHas('enrolledCount', 2);
        $response->assertViewHas('completedCount', 1);
        // (50% + 100%) / 2 = 75.0%
        $response->assertViewHas('overallProgress', 75.0);
        // Next lesson to continue should be Course 1 Lesson 2
        $response->assertViewHas('primaryNextLesson', function ($lesson) use ($lesson1_2) {
            return $lesson->id === $lesson1_2->id;
        });
        $response->assertSee('Advanced Lesson 2');
    }

    public function test_student_dashboard_handles_empty_enrollments_cleanly(): void
    {
        $newStudent = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $newStudent->assignRole(Role::STUDENT);

        $response = $this->actingAs($newStudent)->get('/student/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('enrolledCount', 0);
        $response->assertViewHas('completedCount', 0);
        $response->assertViewHas('overallProgress', 0.0);
        $response->assertViewHas('primaryNextLesson', null);
        $response->assertSee('You are not enrolled in any courses yet');
    }

    public function test_instructor_dashboard_calculates_exact_metrics_and_newest_students(): void
    {
        $category = Category::factory()->create();

        // Instructor has 2 courses: 1 published, 1 draft
        $coursePub = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
        ]);
        $courseDraft = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'approval_status' => Course::APPROVAL_DRAFT,
            'publication_status' => Course::PUB_DRAFT,
        ]);

        $s1 = User::factory()->create();
        $s2 = User::factory()->create();
        $s3 = User::factory()->create();

        // s1 enrolled in coursePub
        CourseEnrollment::create([
            'user_id' => $s1->id,
            'course_id' => $coursePub->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now()->subDays(3),
        ]);

        // s2 enrolled in coursePub
        CourseEnrollment::create([
            'user_id' => $s2->id,
            'course_id' => $coursePub->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now()->subDay(),
        ]);

        // s3 enrolled in coursePub
        CourseEnrollment::create([
            'user_id' => $s3->id,
            'course_id' => $coursePub->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now(),
        ]);

        // Another instructor's course with s1 shouldn't duplicate or inflate counts
        $otherInstructor = User::factory()->create();
        $otherCourse = Course::factory()->create(['instructor_id' => $otherInstructor->id]);
        CourseEnrollment::create([
            'user_id' => $s1->id,
            'course_id' => $otherCourse->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($this->instructor)->get('/instructor/dashboard');
        $response->assertStatus(200);

        $response->assertViewHas('totalCourses', 2);
        $response->assertViewHas('publishedCourses', 1);
        $response->assertViewHas('draftCourses', 1);
        $response->assertViewHas('totalStudents', 3);
        $response->assertViewHas('newestEnrollments', function ($enrollments) {
            return $enrollments->count() === 3;
        });
    }

    public function test_instructor_dashboard_handles_empty_courses_cleanly(): void
    {
        $newInstructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $newInstructor->assignRole(Role::INSTRUCTOR);

        $response = $this->actingAs($newInstructor)->get('/instructor/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('totalCourses', 0);
        $response->assertViewHas('totalStudents', 0);
        $response->assertViewHas('publishedCourses', 0);
        $response->assertSee('Create Your First Course');
        $response->assertSee('No student enrollments yet.');
    }

    public function test_admin_dashboard_calculates_exact_institutional_aggregates(): void
    {
        // Add known accounts:
        // Already have: 1 admin ($this->admin), 1 instructor ($this->instructor), 1 student ($this->student)
        // Add 2 more students -> total students = 3
        $s1 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $s1->assignRole(Role::STUDENT);
        $s2 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $s2->assignRole(Role::STUDENT);

        // Add 1 pending instructor -> total instructors = 2, pending instructors = 1
        $pendingInst = User::factory()->create(['status' => User::STATUS_PENDING]);
        $pendingInst->assignRole(Role::INSTRUCTOR);

        // Add courses:
        // 2 published, 1 pending approval
        $category = Category::factory()->create();
        Course::factory()->create([
            'category_id' => $category->id,
            'publication_status' => Course::PUB_PUBLISHED,
            'approval_status' => Course::APPROVAL_APPROVED,
        ]);
        Course::factory()->create([
            'category_id' => $category->id,
            'publication_status' => Course::PUB_PUBLISHED,
            'approval_status' => Course::APPROVAL_APPROVED,
        ]);
        Course::factory()->create([
            'category_id' => $category->id,
            'publication_status' => Course::PUB_DRAFT,
            'approval_status' => Course::APPROVAL_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        $response->assertViewHas('totalStudents', 3);
        $response->assertViewHas('totalInstructors', 2);
        $response->assertViewHas('publishedCoursesCount', 2);
        $response->assertViewHas('pendingCoursesCount', 1);
        $response->assertViewHas('pendingInstructorsCount', 1);
    }
}
