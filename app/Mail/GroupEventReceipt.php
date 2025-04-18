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

class GroupEventReceipt extends Mailable
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
     */
    public function build(): static
    {
        $orgName = $this->org->orgName;

        //dd($this->array);
        return $this
            ->subject("$orgName Group Registration Confirmation")
            ->view('v1.auth_pages.events.registration.group_receipt')
            ->with([
                'event' => $this->array['event'], 'quantity' => $this->array['quantity'],
                'loc' => $this->array['loc'],
                'rf' => $this->rf, 'person' => $this->array['person'], 'org' => $this->array['org'],
            ])->attach($this->attachment);
    }
}
