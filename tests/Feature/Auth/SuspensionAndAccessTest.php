<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspensionAndAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login_from_all_portals(): void
    {
        $portals = [
            '/student/dashboard',
            '/instructor/dashboard',
            '/admin/dashboard',
            '/super-admin/dashboard',
            '/profile',
        ];

        foreach ($portals as $portal) {
            $response = $this->get($portal);
            $response->assertRedirect('/login');
        }
    }

    public function test_student_can_only_access_student_dashboard(): void
    {
        $student = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $student->assignRole(Role::STUDENT);

        $this->actingAs($student)->get('/student/dashboard')->assertStatus(200);

        $this->actingAs($student)->get('/instructor/dashboard')->assertStatus(403);
        $this->actingAs($student)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($student)->get('/super-admin/dashboard')->assertStatus(403);
    }

    public function test_instructor_can_only_access_instructor_dashboard(): void
    {
        $instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $instructor->assignRole(Role::INSTRUCTOR);

        $this->actingAs($instructor)->get('/instructor/dashboard')->assertStatus(200);

        $this->actingAs($instructor)->get('/student/dashboard')->assertStatus(403);
        $this->actingAs($instructor)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($instructor)->get('/super-admin/dashboard')->assertStatus(403);
    }

    public function test_admin_can_only_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);

        $this->actingAs($admin)->get('/student/dashboard')->assertStatus(403);
        $this->actingAs($admin)->get('/instructor/dashboard')->assertStatus(403);
        $this->actingAs($admin)->get('/super-admin/dashboard')->assertStatus(403);
    }

    public function test_super_admin_can_access_super_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $superAdmin->assignRole(Role::SUPER_ADMIN);

        $this->actingAs($superAdmin)->get('/super-admin/dashboard')->assertStatus(200);
    }

    public function test_active_session_with_suspended_user_is_blocked_and_logged_out(): void
    {
        $user = User::factory()->create(['status' => User::STATUS_SUSPENDED]);
        $user->assignRole(Role::STUDENT);

        $response = $this->actingAs($user)->get('/student/dashboard');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }
}
