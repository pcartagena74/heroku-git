<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OrgPerson;

class OrgPersonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrgPerson::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'OrgStat1' => $this->faker->unique()->randomNumber(rand(5, 7)),
        ];
    }
}
