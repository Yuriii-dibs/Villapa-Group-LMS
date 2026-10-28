<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Register as Student');
    }

    public function test_new_users_can_register_and_are_assigned_student_role_only(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isStudent());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isInstructor());
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
        $this->assertNotNull($user->last_login_at);

        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_registration_strictly_ignores_role_manipulation_attempts(): void
    {
        $response = $this->post('/register', [
            'name' => 'Attacker User',
            'email' => 'attacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => Role::SUPER_ADMIN,
            'roles' => [Role::SUPER_ADMIN, Role::ADMIN],
            'is_admin' => true,
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'attacker@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isStudent());
        $this->assertFalse($user->hasRole(Role::SUPER_ADMIN));
        $this->assertFalse($user->hasRole(Role::ADMIN));
        $this->assertFalse($user->hasRole(Role::INSTRUCTOR));
        $this->assertCount(1, $user->roles);

        $response->assertRedirect(route('student.dashboard'));
    }
}
