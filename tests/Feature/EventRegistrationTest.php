<?php

namespace Tests\Feature;

use App\Event;
use App\Org;
use App\User;
use App\Person;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Faker\Generator as Faker;

class EventRegistrationTest extends TestCase
{
    use WithFaker;

    // , RefreshDatabase;

    /**
     * @test - Member Creation Test
     *         Member defined as org-person.OrgStat1 is not null
     */
    public function a_member_can_be_created()
    {
        //$this->withoutExceptionHandling();
        $this->actingAs($this->user);

        $new_member = factory('App\Person')
            ->create([
                'defaultOrgID' => $this->org->orgID,
                'defaultOrgPersonID' => 0,  // temporary value for DB integrity constraint
            ]);

        $new_member->orgperson()->create(factory('App\OrgPerson')
            ->raw([
                'orgID' => $this->org->orgID,
                'personID' => $new_member->personID,
                'OrgStat1' => $this->faker->unique()->randomNumber(rand(5, 7)),
            ]));

        $new_member->emails()->create(factory('App\Email')
            ->raw([
                'personID' => $new_member->personID,
                'emailADDR' => $new_member->login,
                'isPrimary' => 1]));

        $new_member->user()->create(factory('App\User')
            ->raw([
                'id' => $new_member->personID,
                'name' => $new_member->login,
                'email' => $new_member->login,
                'login' => $new_member->login,
            ]));

        $this->assertDatabaseHas('person', [
            'personID' => $new_member->personID
        ]);

        $this->assertDatabaseHas('person-email', [
            'personID' => $new_member->personID,
            'emailADDR' => $new_member->login
        ]);

        $this->assertDatabaseHas('org-person', [
            'personID' => $new_member->personID,
            'orgID' => $new_member->defaultOrgID
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $new_member->personID,
        ]);
    }

    /**
     * @test - Non-Member Creation Test
     *         Non-Member defined as org-person.OrgStat1 is null
     */
    public function a_nonmember_can_be_created()
    {
        //$this->withoutExceptionHandling();
        $this->actingAs($this->user);

        $new_nonmember = factory('App\Person')
            ->create([
                'defaultOrgID' => $this->org->orgID,
                'defaultOrgPersonID' => 0,  // temporary value for DB integrity constraint
            ]);

        $new_nonmember->orgperson()->create(factory('App\OrgPerson')
            ->raw([
                'orgID' => $this->org->orgID,
                'personID' => $new_nonmember->personID,
                'OrgStat1' => null,
            ]));

        $new_nonmember->emails()->create(factory('App\Email')
            ->raw([
                'personID' => $new_nonmember->personID,
                'emailADDR' => $new_nonmember->login,
                'isPrimary' => 1]));

        $new_nonmember->user()->create(factory('App\User')
            ->raw([
                'id' => $new_nonmember->personID,
                'name' => $new_nonmember->login,
                'email' => $new_nonmember->login,
                'login' => $new_nonmember->login,
            ]));

        $this->assertDatabaseHas('person', [
            'personID' => $new_nonmember->personID
        ]);

        $this->assertDatabaseHas('person-email', [
            'personID' => $new_nonmember->personID,
            'emailADDR' => $new_nonmember->login
        ]);

        $this->assertDatabaseHas('org-person', [
            'personID' => $new_nonmember->personID,
            'orgID' => $new_nonmember->defaultOrgID
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $new_nonmember->personID,
        ]);
    }

    /**
     * @test - Member Registration Test
     *
     * depends an_event_can_be_created - $event
     * depends a_member_can_be_created - $new_member
     *
     */

    /*
     * switching to dusk for browser-based testing for registration
     *

    public function a_member_can_register_for_an_event()
    {
    $event = Event::all()->random(1);

    $this->visit("/events/$event->slug")
        ->seePageIs("/events/$event->slug");
    }

    /**
     * @test - Non-Member Registration Test
     *
     * @depends an_event_can_be_created - $event
     * @depends a_nonmember_can_be_created - $new_nonmember
     *
     */

    /*
     * switching to dusk for browser-based testing for registration
     *
    public function a_nonmember_can_register_for_an_event()
    {

    }
*/
}
