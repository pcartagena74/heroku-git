<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Event;

class RegisterTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
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
     * @throws \Throwable
     */
    public function RegistrationPageTest()
    {
        $event = Event::all()->random(1)->first();

        $this->browse(function (Browser $browser) use ($event) {
            $browser->visit(env('APP_URL')."/events/$event->slug")
                ->assertSee($event->eventName);
        });

    }
}
