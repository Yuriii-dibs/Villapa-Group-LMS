<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUserSeeder extends Seeder
{
    /**
     * Seed sample accounts for Super Admin, Admin, Instructor, Student, Pending, and Suspended users.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@school.test',
                'password' => 'Password123!',
                'role' => Role::SUPER_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'name' => 'System Administrator',
                'email' => 'admin@villapa-lms.test',
                'password' => 'Password123!',
                'role' => Role::SUPER_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'name' => 'School Administrator',
                'email' => 'admin@school.test',
                'password' => 'Password123!',
                'role' => Role::ADMIN,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'name' => 'Prof. Alex Instructor',
                'email' => 'instructor@school.test',
                'password' => 'Password123!',
                'role' => Role::INSTRUCTOR,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'name' => 'Dr. Maria Santos',
                'email' => 'instructor2@school.test',
                'password' => 'Password123!',
                'role' => Role::INSTRUCTOR,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'name' => 'Mark Pending',
                'email' => 'pending.instructor@school.test',
                'password' => 'Password123!',
                'role' => Role::INSTRUCTOR,
                'status' => User::STATUS_PENDING,
            ],
            [
                'name' => 'Sam Student',
                'email' => 'student@school.test',
                'password' => 'Password123!',
                'role' => Role::STUDENT,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'name' => 'Jane Student',
                'email' => 'student2@school.test',
                'password' => 'Password123!',
                'role' => Role::STUDENT,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'name' => 'Suspended Student',
                'email' => 'suspended@school.test',
                'password' => 'Password123!',
                'role' => Role::STUDENT,
                'status' => User::STATUS_SUSPENDED,
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrNew(['email' => $account['email']]);
            $user->name = $account['name'];
            $user->password = Hash::make($account['password']);
            $user->status = $account['status'];
            $user->email_verified_at = now();
            $user->save();

            // Sync role to ensure single correct role assignment
            $roleModel = Role::where('name', $account['role'])->first();
            if ($roleModel) {
                $user->roles()->sync([$roleModel->id]);
            }
        }
    }
}
