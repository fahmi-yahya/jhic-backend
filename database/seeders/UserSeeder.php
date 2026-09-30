<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\RoleTemplates;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::create([
            'name' => 'Sarah Malik',
            'email' => 'sarah.malik@sekolah.sch.id',
            'password' => Hash::make('password123'), // ganti setelah login pertama
            'role' => 'superadmin',
            'status' => 'active',
            'require_password_reset' => false,
        ]);

        foreach (RoleTemplates::forRole('superadmin') as $module => $perm) {
            $superadmin->permissions()->create([
                'module' => $module,
                'can_view' => $perm['view'],
                'can_edit' => $perm['edit'],
                'can_delete' => $perm['del'],
            ]);
        }
    }
}
