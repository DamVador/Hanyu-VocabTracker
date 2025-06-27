<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AssignAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $regularRole = Role::where('name', 'regular')->first();

        $user = User::first();

        if ($user && $adminRole) {
            $user->assignRole('admin'); 
            $this->command->info('Assigned admin role to user: ' . $user->email);
        } else {
            $this->command->error('Admin role or first user not found. Please ensure users and roles are seeded.');
        }

        User::whereDoesntHave('roles')->get()->each(function ($user) use ($regularRole) {
            if ($regularRole) {
                $user->assignRole('regular');
            }
        });
    }
}