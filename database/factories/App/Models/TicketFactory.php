<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Ticket;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ticketLabel' => $this->faker->word,

        ];
    }
}
