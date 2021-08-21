<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Email;

class EmailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Email::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
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
                'emailADDR' => $this->faker->unique()->safeEmail,
            ];
        } else {
            return [
                'personID' => $personID,
                'emailADDR' => $this->faker->unique()->safeEmail,
            ];
        }
    }
}
