<?php

namespace Tests\Browser;

use App\Email;
use App\Event;
use App\EventDiscount;
use App\EventSession;
use App\Location;
use App\Org;
use App\OrgPerson;
use App\Person;
use App\Ticket;
use App\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class EventRegistrationMemberTest extends DuskTestCase
{
    /**
     * Create user Test buy ticket member and non member (without PMIID)
     *
     * @return void
     */
    private $person;
    private $user;
    private $org_person;
    private $person_member;
    private $user_member;
    private $org_person_member;
    private $event;
    private $event_ticket;
    private $email;
    private $email_member;
    private $ticket;
    private $location;
    private $session;
    private $event_discount_100;
    private $event_discount_20;
    private $test1;
    private $test2;

    public function setUp(): void
    {

        parent::setUp();
        $this->setData();

    }
    public function setData()
    {
        $this->test1 = false;
        $this->test2 = false;
        DB::beginTransaction();
        try {
            $faker  = Faker::create();
            $org_id = 10;
            $org    = Org::find(10);
            // person without PMIID org stat1 = null and rel 1 -4 null
            $p_data = [
                'prefix'       => str_replace('.', '', $faker->titleMale),
                'firstName'    => $faker->firstNameMale,
                'prefName'     => $faker->firstNameMale,
                'midName'      => $faker->lastName,
                'lastName'     => $faker->lastName,
                'suffix'       => $faker->suffix,
                'title'        => $faker->jobTitle,
                'compName'     => $faker->company,
                'login'        => $faker->safeEmail,
                'creatorID'    => 1,
                'defaultOrgID' => $org_id,
                'affiliation'  => null,
                'indName'      => 'Aerospace',
            ];
            $person = factory(Person::class)->create($p_data);
            $email  = $person->login;
            $u_data = [
                'id'    => $person->personID,
                'login' => $email,
                'name'  => $email,
                'email' => $email,
            ];
            $user  = factory(User::class)->create($u_data);
            $email = factory(Email::class, ['person' => $person])->create([
                'personID'  => $person->personID,
                'emailADDR' => $person->login,
                'isPrimary' => 1,
            ]);
            $op_data = ['orgID' => $person->defaultOrgID,
                'personID'          => $person->personID,
                'OrgStat1'          => null,
                'OrgStat2'          => null,
                'OrgStat3'          => null,
                'OrgStat3'          => null,
                'OrgStat4'          => null,
                'RelDate1'          => null,
                'RelDate2'          => null,
                'RelDate3'          => null,
                'RelDate4'          => null,
                'creatorID'         => 1,
            ];
            $org_person = factory(OrgPerson::class)->create($op_data);
            // person without PMIID org stat1 = null and rel 1 -4 null ends
            //
            // person with PMIID org stat1 = null and rel 1 -4 future starts
            $p_data = [
                'prefix'       => str_replace('.', '', $faker->titleMale),
                'firstName'    => $faker->firstNameMale,
                'prefName'     => $faker->firstNameMale,
                'midName'      => $faker->lastName,
                'lastName'     => $faker->lastName,
                'suffix'       => $faker->suffix,
                'title'        => $faker->jobTitle,
                'compName'     => $faker->company,
                'login'        => $faker->safeEmail,
                'creatorID'    => 1,
                'defaultOrgID' => $org_id,
                'affiliation'  => null,
                'indName'      => 'Aerospace',
            ];

            $person_member = factory(Person::class)->create($p_data);
            $email_member  = $person_member->login;
            $u_data        = [
                'id'    => $person_member->personID,
                'login' => $email_member,
                'name'  => $email_member,
                'email' => $email_member,
            ];
            $user_member  = factory(User::class)->create($u_data);
            $email_member = factory(Email::class, ['person' => $person])->create([
                'personID'  => $person_member->personID,
                'emailADDR' => $person_member->login,
                'isPrimary' => 1,
            ]);
            $op_data = ['orgID' => $person_member->defaultOrgID,
                'personID'          => $person_member->personID,
                'OrgStat1'          => $faker->randomNumber,
                'OrgStat2'          => 'Individual',
                'OrgStat3'          => null,
                'OrgStat3'          => null,
                'OrgStat4'          => null,
                'RelDate1'          => Carbon::now()->addYear(),
                'RelDate2'          => Carbon::now()->addYear(),
                'RelDate3'          => null,
                'RelDate4'          => null,
                'creatorID'         => 1,
            ];
            $org_person_member = factory(OrgPerson::class)->create($op_data);

            // person with PMIID org stat1 = null and rel 1 -4 future ends
            $loc_data = [
                'locName'   => $faker->cityPrefix,
                'addr1'     => $faker->streetAddress,
                'addr2'     => $faker->secondaryAddress,
                'city'      => $faker->city,
                'state'     => $faker->stateAbbr,
                'zip'       => $faker->postcode,
                'orgID'     => $org_id,
                'isVirtual' => 0,
                'updaterID' => 1,
            ];
            $loc = factory(Location::class)->create($loc_data);
            //
            // $factory = new Factory();
            // $loc     = $factory->define(Ticket::class, function (Faker\Generator $faker) use ($loc_data) {
            // return $loc_data;
            // });

            $e_data = [
                'locationID'       => $loc->locID,
                'orgID'            => $org_id,
                'eventName'        => $faker->name,
                'eventDescription' => $faker->sentence,
                'eventInfo'        => $faker->sentence,
                'catID'            => 1,
                'eventTypeID'      => 1,
                'eventStartDate'   => Carbon::now(+5),
                'eventEndDate'     => Carbon::now(+6),
                'eventTimeZone'    => '-0500', //newyork
                'contactOrg'       => $faker->company,
                'contactEmail'     => $faker->safeEmail,
                'contactDetails'   => $faker->sentence,
                'showLogo'         => 0,
                'hasFood'          => 0,
                'slug'             => $faker->slug,
                'postRegInfo'      => $faker->sentence,
                'confDays'         => 0,
                'hasTracks'        => 0,
                'earlyDiscount'    => $org->earlyBirdPercent,
                'earlyBirdDate'    => Carbon::now(),
                'creatorID'        => $person->personID,
                'updaterID'        => $person->personID,
                'isActive'         => 1,
            ];
            $event    = factory(Event::class)->create($e_data);
            $discount = ['orgID' => $org_id,
                'eventID'            => $event->eventID,
                'discountCODE'       => 'Nonprofit',
                'percent'            => 20];
            $event_discount_20 = factory(EventDiscount::class)->create($discount);
            $discount          = ['orgID' => $org_id,
                'eventID'                     => $event->eventID,
                'discountCODE'                => 'Volunteer',
                'percent'                     => 100];
            $event_discount_100 = factory(EventDiscount::class)->create($discount);

            // Create a stub for the default ticket for the event
            $tkt_data = [
                'ticketLabel'         => $org->defaultTicketLabel,
                'availabilityEndDate' => $event->eventStartDate,
                'eventID'             => $event->eventID,
                'earlyBirdPercent'    => $org->earlyBirdPercent,
                'earlyBirdEndDate'    => Carbon::now(),
                'memberBasePrice'     => 10,
                'nonmbrBasePrice'     => 20,

            ];
            $tkt = factory(Ticket::class)->create($tkt_data);

            // Create a mainSession for the default ticket for the event
            $sess_data = [
                'trackID'     => 0,
                'eventID'     => $event->eventID,
                'ticketID'    => $tkt->ticketID,
                'sessionName' => 'def_sess',
                'confDay'     => 0,
                'start'       => $event->eventStartDate,
                'end'         => $event->eventEndDate,
                'order'       => 0,
                'creatorID'   => $person->personID,
                'updaterID'   => $person->personID,
            ];
            $mainSession        = factory(EventSession::class)->create($sess_data);
            $event->mainSession = $mainSession->sessionID;
            $event->updaterID   = $person->personID;
            $event->save();

            $this->person             = $person;
            $this->user               = $user;
            $this->email              = $email;
            $this->org_person         = $org_person;
            $this->person_member      = $person_member;
            $this->user_member        = $user_member;
            $this->email_member       = $email_member;
            $this->org_person_member  = $org_person_member;
            $this->event              = $event;
            $this->event_ticket       = $tkt;
            $this->location           = $loc;
            $this->session            = $mainSession;
            $this->event_discount_20  = $event_discount_20;
            $this->event_discount_100 = $event_discount_100;
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
        }
    }
    /**
     * scenario we are testing
     * Use the factory to create a user/person connected to the proper orgID WITH a PMI_ID (OrgStat1) and unexpired (future) dates  for RelDate3 & RelDate4
    Use the factory to create a user/person WITHOUT a PMI_ID (OrgStat1 = null, RelDates1-4 = null);
    Use the factory to create an event with different member and non-member pricing > $0 with all relevant populated dates in the future.
    Take note of the default discount codes that exist. Use one of the 20% discounts and one of the 100% discounts (in registration examples below)
     *
     **/

    /** @test */
    public function testEventRegistrationNonMemberWithDiscount()
    {
        $user   = $this->user;
        $event  = $this->event;
        $ticket = $this->event_ticket;
        $this->browse(function ($browser) use ($user, $event, $ticket) {
            $mail            = $browser->fake(Mail::class);
            $stripeFrameName = '__privateStripeFrame5';
            $selector        = 'iframe[name=' . $stripeFrameName . ']';
            $browser->loginAs($user)
                ->visit('/events/' . $event->slug)
                ->type('input[name="q-' . $ticket->ticketID . '"]', 1)
                ->screenshot('events/' . $event->slug)
                ->press('Purchase Ticket(s)')
                ->assertPathIs('/regstep2/' . $event->eventID . '/1')
                ->type('#discount_code', 'Nonprofit')
                ->press('#btn-apply')
                ->pause(500)
                ->assertSeeIn('#final1', '16.00')
                ->press('Next: Review & Payment')
                ->assertPathIs('/confirm_registration/*')
                ->press('#payment')
                ->screenshot('events/' . $event->slug . '- dialog')
                ->waitfor('#stripe_modal iframe')
                ->withinFrame($selector, function ($browser) {
                    $browser->keys('input[name="cardnumber"]', '4111 1111 1111 1111')
                        ->keys('input[name="exp-date"]', '12 22')
                        ->keys('input[name="cvc"]', '123')
                        ->keys('input[placeholder="ZIP"]', '10001');
                    // ->press('button[type="submit"')
                    // ->waitUntilMissing('iframe[name=stripe_checkout_app]');
                })->press('Pay $' . 16) //non member base price is 20 and 20% discount
                ->waitUntilMissing($selector)
                ->assertPathIs('/show_receipt/*');
            //check email
            $mail->assertSent(EventReceipt::class);
            //check receipt
            $registration = RegFinance::where(['event_id' => $event->eventID, 'personID' => $user_member->id])->get()->first();
            $file_name    = $registration->eventID . "/" . $registration->confirmation . ".pdf";
            /**** read below ***/
            Storage::disk('s3_receipts')->assertExists($file_name);
            /**** read below ***/
            Storage::disk('local')->assertExists("public/avatars/{$file->hashName()}");
            // check receipt file on s3 pending as snappypdf not working on local debugged for more than 2 hours now skipping for now
            /**** read below ends ***/

        });
    }
    /** @test */
    public function testEventRegistrationMemberWithDiscount()
    {
        $user_member = $this->user_member;
        $event       = $this->event;
        $ticket      = $this->event_ticket;
        //assertion for member
        $this->browse(function ($browser) use ($user_member, $event, $ticket) {
            $mail = $browser->fake(Mail::class);
            $browser->loginAs($user_member)
                ->visit('/events/' . $event->slug)
                ->type('input[name="q-' . $ticket->ticketID . '"]', 1)
                ->press('Purchase Ticket(s)')
                ->assertPathIs('/regstep2/' . $event->eventID . '/1')
                ->type('#discount_code', 'Volunteer')
                ->press('#btn-apply')
                ->pause(500)
                ->assertSeeIn('#final1', '0.00')
                ->press('Next: Review & Payment')
                ->assertPathIs('/confirm_registration/*')
                ->press('#nocard')->screenshot('events/' . $event->slug)
                ->assertPathIs('/show_receipt/*');

            //check email
            $mail->assertSent(EventReceipt::class);

            //check receipt
            $registration = RegFinance::where(['event_id' => $event->eventID, 'personID' => $user_member->id])->get()->first();
            $file_name    = $registration->eventID . "/" . $registration->confirmation . ".pdf";
            /**** read below ***/
            Storage::disk('s3_receipts')->assertExists($file_name);
            // check receipt file on s3 pending as snappypdf not working on local debugged for more than 2 hours now skipping for now
            /**** read below ends ***/

        });

    }

    public function tearDown(): void
    {
        parent::tearDown();
        if ($this->test1 && $this->test2) {
            $this->person->delete();
            $this->user->delete();
            $this->org_person->delete();
            $this->person_member->delete();
            $this->user_member->delete();
            $this->org_person_member->delete();
            $this->event->delete();
            $this->event_ticket->delete();
            $this->email->delete();
            $this->email_member->delete();
            $this->ticket->delete();
            $this->location->delete();
            $this->session->delete();
            $this->event_discount_100->delete();
            $this->event_discount_20->delete();

        }
        // var_dump('here');
    }
    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@ticketInput' => '#q-',
        ];
    }
}
