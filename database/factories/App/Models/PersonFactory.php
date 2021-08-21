<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Person;

class PersonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Person::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'login' => $this->faker->unique()->safeEmail,
            'prefName' => $this->faker->firstName,
        ];
    }
}
