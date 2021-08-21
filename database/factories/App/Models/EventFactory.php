<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories\App\Models;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Event;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $m = rand(1, 5);
        $tz = '-0500';
        $future_date = Carbon::create(
        Carbon::now()->addMonth($m)->year,
        Carbon::now()->addMonth($m)->month,
        Carbon::now()->day,
        rand(0, 23), 0, 0, $tz);

        return [
            'eventName' => $this->faker->sentence(4),
            'eventDescription' => $this->faker->paragraph,
            'eventStartDate' => $future_date,
            'eventEndDate' => $future_date->addHour(1),
            'eventTimeZone' => $tz,
            'eventTypeID' => 1,
            'slug' => $this->faker->unique()->word,
            'locationID' => 1,
        ];
    }
}
