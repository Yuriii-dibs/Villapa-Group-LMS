<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            Role::SUPER_ADMIN => [
                'display_name' => 'Super Admin',
                'description' => 'Unrestricted institutional control and system configuration.',
            ],
            Role::ADMIN => [
                'display_name' => 'Admin',
                'description' => 'Institutional operations, user administration, and academic oversight.',
            ],
            Role::INSTRUCTOR => [
                'display_name' => 'Instructor',
                'description' => 'Course design, instruction, and student mentorship.',
            ],
            Role::STUDENT => [
                'display_name' => 'Student',
                'description' => 'Course enrollment, content consumption, and learning progress.',
            ],
        ];

        $roleModels = [];
        foreach ($roles as $name => $attributes) {
            $roleModels[$name] = Role::firstOrCreate(
                ['name' => $name],
                $attributes
            );
        }

        $permissions = [
            'view:super-admin-dashboard' => ['display_name' => 'View Super Admin Dashboard', 'roles' => [Role::SUPER_ADMIN]],
            'view:admin-dashboard' => ['display_name' => 'View Admin Dashboard', 'roles' => [Role::SUPER_ADMIN, Role::ADMIN]],
            'view:instructor-dashboard' => ['display_name' => 'View Instructor Dashboard', 'roles' => [Role::SUPER_ADMIN, Role::INSTRUCTOR]],
            'view:student-dashboard' => ['display_name' => 'View Student Dashboard', 'roles' => [Role::SUPER_ADMIN, Role::ADMIN, Role::INSTRUCTOR, Role::STUDENT]],
            'manage:system' => ['display_name' => 'Manage System Settings', 'roles' => [Role::SUPER_ADMIN]],
            'manage:users' => ['display_name' => 'Manage Users and Roles', 'roles' => [Role::SUPER_ADMIN, Role::ADMIN]],
            'manage:courses' => ['display_name' => 'Manage All Courses', 'roles' => [Role::SUPER_ADMIN, Role::ADMIN]],
            'create:courses' => ['display_name' => 'Create Courses', 'roles' => [Role::SUPER_ADMIN, Role::ADMIN, Role::INSTRUCTOR]],
            'enroll:courses' => ['display_name' => 'Enroll in Courses', 'roles' => [Role::SUPER_ADMIN, Role::STUDENT]],
        ];

        foreach ($permissions as $permName => $data) {
            $permission = Permission::firstOrCreate(
                ['name' => $permName],
                ['display_name' => $data['display_name']]
            );

            foreach ($data['roles'] as $roleName) {
                if (isset($roleModels[$roleName])) {
                    $roleModels[$roleName]->permissions()->syncWithoutDetaching([$permission->id]);
                }
            }
        }
    }
}
