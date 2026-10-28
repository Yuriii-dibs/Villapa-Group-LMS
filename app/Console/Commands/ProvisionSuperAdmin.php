<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProvisionSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:provision-super-admin 
                            {--name= : The name of the Super Admin} 
                            {--email= : The email of the Super Admin} 
                            {--password= : The initial password for the Super Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provision or promote a user to Super Admin safely via CLI';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Enter the Super Admin name');
        $email = $this->option('email') ?: $this->ask('Enter the Super Admin email');
        $password = $this->option('password') ?: $this->secret('Enter the Super Admin password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $superAdminRole = Role::firstOrCreate(
            ['name' => Role::SUPER_ADMIN],
            [
                'display_name' => 'Super Admin',
                'description' => 'Unrestricted institutional control and system configuration.',
            ]
        );

        DB::transaction(function () use ($name, $email, $password, $superAdminRole, &$user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->name = $name;
                if (! empty($password)) {
                    $user->password = Hash::make($password);
                }
                $user->status = User::STATUS_ACTIVE;
                $user->save();
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => User::STATUS_ACTIVE,
                ]);
            }

            $user->assignRole($superAdminRole);
        });

        $this->info("Super Admin [{$user->email}] provisioned successfully with active status.");

        return self::SUCCESS;
    }
}
