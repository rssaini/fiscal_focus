<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create(['name' => 'Admin']);
        $permission = Permission::create(['name' => 'create-User']);
        $permission->assignRole($role);
        $permission = Permission::create(['name' => 'view-User']);
        $permission->assignRole($role);
        $permission = Permission::create(['name' => 'edit-User']);
        $permission->assignRole($role);
        $permission = Permission::create(['name' => 'delete-User']);
        $permission->assignRole($role);

        $user = User::updateOrCreate(
            [
                'email' => 'admin@localhost.com',
                'name' => 'Admin User',
                'password' => Hash::make('password123'), // secure password
            ]
        );
        $user->assignRole($role);
    }
}
