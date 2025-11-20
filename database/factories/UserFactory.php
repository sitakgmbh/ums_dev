<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            "username"   => $this->faker->unique()->userName(),
            "auth_type"  => "local",
            "firstname"  => $this->faker->firstName(),
            "lastname"   => $this->faker->lastName(),
            "email"      => $this->faker->unique()->safeEmail(),
            "password"   => bcrypt("Password!"),
            "is_enabled" => true,
        ];
    }
}
