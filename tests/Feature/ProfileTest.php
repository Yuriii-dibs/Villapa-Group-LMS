<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::STUDENT);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Your Profile');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::STUDENT);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
    }

    public function test_user_cannot_escalate_role_via_profile_update(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::STUDENT);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Attacker Profile',
            'email' => 'attacker_profile@example.com',
            'role' => Role::SUPER_ADMIN,
            'roles' => [Role::SUPER_ADMIN, Role::ADMIN],
            'status' => 'super-admin',
        ]);

        $user->refresh();
        $this->assertTrue($user->isStudent());
        $this->assertFalse($user->hasRole(Role::SUPER_ADMIN));
        $this->assertFalse($user->hasRole(Role::ADMIN));
        $this->assertCount(1, $user->roles);
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password123'),
        ]);
        $user->assignRole(Role::STUDENT);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'old-password123',
            'password' => 'new-password456',
            'password_confirmation' => 'new-password456',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-password456', $user->fresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password123'),
        ]);
        $user->assignRole(Role::STUDENT);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'wrong-current-password',
            'password' => 'new-password456',
            'password_confirmation' => 'new-password456',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('old-password123', $user->fresh()->password));
    }
}
