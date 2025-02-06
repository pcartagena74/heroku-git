<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Location;
use App\Models\Org;
use App\Models\RegFinance;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $rf;

    public $event;

    public $org;

    public $loc;

    public $tkt;

    public $array;

    public $attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(RegFinance $rf, $pdf_path, $x)
    {
        $this->rf = $rf;
        $this->event = Event::find($this->rf->eventID);
        $this->org = Org::find($this->event->orgID);
        $this->loc = Location::find($this->event->locationID);
        $this->tkt = Ticket::find($this->rf->ticketID);
        $this->array = $x;
        $this->attachment = $pdf_path;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $orgName = $this->org->orgName;

        //dd($this->array);
        return $this
            ->subject("$orgName Event Registration Confirmation")
            ->view('v1.public_pages.event_receipt')
            ->with([
                'needSessionPick' => $this->array['needSessionPick'], 'ticket' => $this->array['ticket'],
                'event' => $this->array['event'], 'quantity' => $this->array['quantity'],
                'discount_code' => $this->array['discount_code'], 'loc' => $this->array['loc'],
                'rf' => $this->rf, 'person' => $this->array['person'], 'org' => $this->array['org'],
                'tickets' => $this->array['tickets'],
            ])->attach($this->attachment);
    }
}
