<?php

namespace Tests\Feature;

use App\Org;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EventTest extends TestCase
{
    use WithFaker;

    protected $event_attributes;
    protected $future_date;
    protected $org;
    protected $user;
    protected $person;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Org::find(1);
        $this->user = User::find(1);
        $this->person = $this->user->person;

        $m = rand(0, 4);
        $t = rand(0, 23);

        $this->future_date = Carbon::create(
            Carbon::now()->addMonth($m)->year,
            Carbon::now()->addMonth($m)->month,
            Carbon::now()->day,
            $t, 0, 0, $this->org->orgZone);

        $this->event_attributes = [
            'eventName' => $this->faker->sentence(4),
            'eventDescription' => $this->faker->paragraph,
            'eventStartDate' => $this->future_date,
            'eventEndDate' => $this->future_date->addHour(1),
            'eventTimeZone' => $this->org->orgZone,

            // for eventTypeID, think about making it random based on orgID ?
            // Or possibly vary with different tests based on eventTypeID differences
            'eventTypeID' => 1,
            'slug' => $this->faker->word,
            'locationID' => 1,
        ];
    }

    /**
     * @test
     */
    public function only_authenticated_users_can_create_events()
    {
        $attributes = factory(\App\Event::class)->raw(['org' => $this->org]);
        $this->post('/event/create', $attributes)->assertRedirect('login');
    }

    /**
     * @test - Event Creation Test
     *
     * @return void
     */
    public function an_event_can_be_created()
    {
        // Useful function -->  Carbon::setTestNow($datetime);
        //                 -->  Carbon::setTestNow() to reset for subsequent test

        // Future enhancements/additions:
        //   - Add different orgIDs, people defaulted to orgIDs, registering for events of different orgIDs

        //$this->visit('/events/113')->type('1','quantity')->press('Register')->seePageIs('tet');

        $this->withoutExceptionHandling();

        $this->actingAs($this->user);

        $event = $this->person->defaultOrg->events()->create($this->event_attributes);

        $event->tickets()->create(factory(\App\Ticket::class)->raw([
            'eventID' => $event->eventID,
            'availabilityEndDate' => $event->eventStartDate,
            'memberBasePrice' => 25.00,
            'nonmbrBasePrice' => 35.00,
        ]));

        $session = $event->main_session()->create(factory(\App\EventSession::class)->raw([
            'eventID' => $event->eventID,
            'start' => $event->eventStartDate,
            'end' => $event->eventEndDate,
        ]));

        $event->mainSession = $session->sessionID;
        $event->save();

        //$event = $this->post('/event/create', $attributes)->assertRedirect('/manage_events');

        $this->assertDatabaseHas('org-event', $this->event_attributes);

        $this->assertDatabaseHas('event-sessions', [
            'eventID' => $event->eventID,
            'sessionID' => $event->mainSession,
        ]);

        $this->assertDatabaseHas('event-tickets', [
            'eventID' => $event->eventID,
        ]);

        return $event;
        // $response->assertStatus(200);
    }

    /**
     * @test - Ticket Creation Test
     *
     * @depends an_event_can_be_created
     */

    /*
     *
     * Commented out for now because the default ticket was added to the event-creation test
     *
    public function another_ticket_can_be_created_for_an_event($event)
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->user);

        $ticket = $event->tickets()->create(factory('App\Ticket')->raw([
            'eventID' => $event->eventID,
            'availabilityEndDate' => $event->eventStartDate,
            'memberBasePrice' => 25.00,
            'nonmbrBasePrice' => 35.00,
        ]));

        $this->assertDatabaseHas('event-tickets', [
            'ticketID' => $ticket->ticketID
        ]);
        return $ticket;
    }
    */
}
