<?php

namespace Database\Factories\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        try {
            $p = $params['person'];
        } catch (Exception $e) {
            $p = null;
        }

        if ($p !== null) {
            return [
                'name' => $p->login,
                'email' => $p->login,
                'login' => $p->login,
                'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'remember_token' => Str::random(10),
            ];
        } else {
            $e = $this->faker->unique()->safeEmail();

            return [
                'name' => $e,
                'email' => $e,
                'login' => $e,
                'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'remember_token' => Str::random(10),
            ];
        }
    }
}
