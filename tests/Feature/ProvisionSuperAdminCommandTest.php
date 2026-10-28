<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvisionSuperAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_be_provisioned_via_artisan_command(): void
    {
        $this->artisan('app:provision-super-admin', [
            '--name' => 'Root Administrator',
            '--email' => 'superadmin@example.com',
            '--password' => 'SecurePass123!',
        ])->assertSuccessful();

        $user = User::where('email', 'superadmin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isSuperAdmin());
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
    }

    public function test_existing_user_can_be_promoted_to_super_admin_via_command(): void
    {
        $student = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $student->assignRole(Role::STUDENT);

        $this->artisan('app:provision-super-admin', [
            '--name' => $student->name,
            '--email' => $student->email,
            '--password' => 'NewPassword123!',
        ])->assertSuccessful();

        $student->refresh();
        $this->assertTrue($student->isSuperAdmin());
        $this->assertEquals('super-admin', $student->getPrimaryRole());
    }
}
