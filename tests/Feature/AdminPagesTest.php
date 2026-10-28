<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected User $instructor;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => Role::ADMIN], ['display_name' => 'Administrator']);
        $instructorRole = Role::firstOrCreate(['name' => Role::INSTRUCTOR], ['display_name' => 'Instructor']);
        $studentRole = Role::firstOrCreate(['name' => Role::STUDENT], ['display_name' => 'Student']);

        $this->admin = User::factory()->create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->admin->roles()->attach($adminRole);

        $this->instructor = User::factory()->create([
            'name' => 'Professor Smith',
            'email' => 'instructor@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->instructor->roles()->attach($instructorRole);

        $this->student = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'student@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->student->roles()->attach($studentRole);
    }

    public function test_admin_dashboard_renders_stats_and_recent_registrations(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Administrator Console');
        $response->assertSee('Manage Users');
        $response->assertSee('All Courses');
        $response->assertSee('Categories');
        $response->assertSee('Course Approvals');
        $response->assertSee('Recent Registrations');
        $response->assertSee('Alice Johnson');
    }

    public function test_admin_users_page_displays_users_filters_and_modals(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('User Administration');
        $response->assertSee('Filter by Role');
        $response->assertSee('Filter by Status');
        $response->assertSee($this->instructor->name);
        $response->assertSee($this->student->name);
        $response->assertSee('Protected Self Account');
        $response->assertSee('Change Role');
        $response->assertSee('Suspend');
    }

    public function test_admin_pending_courses_page_has_preview_approve_and_reject_actions(): void
    {
        $category = Category::create([
            'name' => 'Computer Science',
            'slug' => 'computer-science',
        ]);

        $pendingCourse = Course::create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'title' => 'Advanced Cybersec Audit',
            'slug' => 'advanced-cybersec-audit',
            'description' => 'A comprehensive review course waiting for admin validation.',
            'difficulty' => Course::DIFFICULTY_ADVANCED,
            'publication_status' => Course::PUB_DRAFT,
            'approval_status' => Course::APPROVAL_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.courses.pending'));

        $response->assertOk();
        $response->assertSee('Pending Course Approvals');
        $response->assertSee('Advanced Cybersec Audit');
        $response->assertSee('Preview Course');
        $response->assertSee('Confirm & Publish', false);
        $response->assertSee('Reject Course & Request Modifications', false);
    }

    public function test_admin_all_courses_page_lists_courses_and_allows_unpublish(): void
    {
        $category = Category::create([
            'name' => 'Web Development',
            'slug' => 'web-development',
        ]);

        $publishedCourse = Course::create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'title' => 'Web Design Basics',
            'slug' => 'web-design-basics',
            'description' => 'Introduction to UI & Web Standards.',
            'difficulty' => Course::DIFFICULTY_BEGINNER,
            'publication_status' => Course::PUB_PUBLISHED,
            'approval_status' => Course::APPROVAL_APPROVED,
            'published_at' => now(),
        ]);

        // Visit All Courses
        $response = $this->actingAs($this->admin)->get(route('admin.courses.index'));
        $response->assertOk();
        $response->assertSee('All Institutional Courses');
        $response->assertSee('Web Design Basics');
        $response->assertSee('Unpublish');
        $response->assertSee('Preview');

        // Execute Unpublish
        $unpublishResponse = $this->actingAs($this->admin)->post(route('admin.courses.unpublish', $publishedCourse));
        $unpublishResponse->assertSessionHas('status');

        $this->assertEquals(Course::PUB_DRAFT, $publishedCourse->fresh()->publication_status);
    }

    public function test_admin_category_management_add_edit_and_delete_protection(): void
    {
        // 1. Create a category
        $storeResponse = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Robotics & AI',
            'description' => 'Exploring modern robotics engineering.',
        ]);
        $storeResponse->assertRedirect(route('admin.categories.index'));
        $storeResponse->assertSessionHas('status');

        $category = Category::where('name', 'Robotics & AI')->firstOrFail();

        // 2. Edit the category
        $editPageResponse = $this->actingAs($this->admin)->get(route('admin.categories.edit', $category));
        $editPageResponse->assertOk();
        $editPageResponse->assertSee('Robotics & AI');

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Robotics & Autonomous Systems',
            'description' => 'Updated syllabus description.',
        ]);
        $updateResponse->assertRedirect(route('admin.categories.index'));
        $updateResponse->assertSessionHas('status');

        // 3. Delete category when no courses are attached
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));
        $deleteResponse->assertRedirect(route('admin.categories.index'));
        $deleteResponse->assertSessionHas('status');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_category_with_courses(): void
    {
        $category = Category::create([
            'name' => 'Mobile Dev',
            'slug' => 'mobile-dev',
        ]);

        Course::create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'title' => 'Flutter Architecture',
            'slug' => 'flutter-architecture',
            'description' => 'Deep dive into state management.',
            'difficulty' => Course::DIFFICULTY_INTERMEDIATE,
            'publication_status' => Course::PUB_DRAFT,
            'approval_status' => Course::APPROVAL_DRAFT,
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));
        $deleteResponse->assertSessionHasErrors(['error']);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
