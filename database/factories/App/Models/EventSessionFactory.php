<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EventSession;

class EventSessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventSession::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sessionName' => $this->faker->word(rand(3, 5)),
            'sessionAbstract' => $this->faker->paragraph(3),
            'maxAttendees' => 50,
        ];
    }
}