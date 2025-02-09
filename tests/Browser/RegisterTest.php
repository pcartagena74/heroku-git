<?php

namespace Tests\Browser;

use App\Models\Event;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RegisterTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     *
     * @throws \Throwable
     */

    /*
    public function testBasicExample()
    {

        $this->browse(function (Browser $browser) {
            $browser->visit(env('APP_URL'))
                ->assertSee('mCentric');
        });
    }
    */

    /**
     * @test A basic browser test for registration.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function RegistrationPageTest(): void
    {
        $event = Event::all()->random(1)->first();

        $this->browse(function (Browser $browser) use ($event) {
            $browser->visit(env('APP_URL')."/events/$event->slug")
                ->assertSee($event->eventName);
        });
    }
}
