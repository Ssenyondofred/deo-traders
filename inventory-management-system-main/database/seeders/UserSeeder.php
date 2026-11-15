<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $staffRole = Role::where('slug', 'staff')->first();

        User::create([
            'role_id' => $adminRole->id,
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@erp.local',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        User::create([
            'role_id' => $staffRole->id,
            'name' => 'Staff Operasional',
            'username' => 'staff',
            'email' => 'staff@erp.local',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
    }
}
