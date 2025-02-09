<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmailFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        try {
            $person = $params['person'];
            $personID = $person->personID;
        } catch (Exception $e) {
            $person = null;
            $personID = null;
        }

        if ($person !== null) {
            return [
                'emailADDR' => $this->faker->unique()->safeEmail(),
            ];
        } else {
            return [
                'personID' => $personID,
                'emailADDR' => $this->faker->unique()->safeEmail(),
            ];
        }
    }
}
