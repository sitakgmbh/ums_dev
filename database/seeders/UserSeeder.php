<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Sicherstellen, dass es Rollen gibt
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole  = Role::firstOrCreate(['name' => 'user']);

        // Admin-User
        $admin = User::factory()->create([
            'username'  => 'admin',
            'firstname' => 'Admin',
            'lastname'  => '',
            'email'     => 'admin@example.com',
            'password'  => bcrypt('Password!'),
        ]);
        $admin->assignRole($adminRole);

        // 20 normale Benutzer
        $users = User::factory()->count(10)->create();
        foreach ($users as $u) {
            $u->assignRole($userRole);
        }
    }
}
