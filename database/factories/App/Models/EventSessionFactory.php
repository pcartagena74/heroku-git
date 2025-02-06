<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sessionName' => $this->faker->word(rand(3, 5)),
            'sessionAbstract' => $this->faker->paragraph(3),
            'maxAttendees' => 50,
        ];
    }
}
