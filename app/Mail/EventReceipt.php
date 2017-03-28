<?php

namespace App\Mail;

use App\Event;
use App\Location;
use App\Org;
use App\RegFinance;
use App\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $rf;
    public $event;
    public $org;
    public $loc;
    public $tkt;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct (RegFinance $rf) {
        $this->rf = $rf;
        $this->event = Event::find($this->rf->eventID);
        $this->org = Org::find($this->event->orgID);
        $this->loc = Location::find($this->event->locationID);
        $this->tkt = Ticket::find($this->rf->ticketID);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build () {
        $orgName = $this->org->orgName;
        return $this
            ->subject("$orgName Event Registration Confirmation")
            ->view('v1.public_pages.event_receipt');
    }
}
