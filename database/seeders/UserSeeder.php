<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(["name" => "admin"]);
        $userRole  = Role::firstOrCreate(["name" => "user"]);

        $admin = User::factory()->create([
            "username" => "admin",
            "firstname" => "Admin",
            "lastname" => "",
            "email" => "admin@sitak.ch",
            "password" => bcrypt("Password!"),
        ]);
        $admin->assignRole($adminRole);

		/*
        $users = User::factory()->count(10)->create();
        foreach ($users as $u) {
            $u->assignRole($userRole);
        }
		*/
    }
}
