<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'description' => 'Administrator with full access']);
        Role::firstOrCreate(['name' => 'regular', 'description' => 'Standard user with basic access']);
        Role::firstOrCreate(['name' => 'premium', 'description' => 'Subscribed user with premium access']);
    }
}
