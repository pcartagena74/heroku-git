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
     *
     * @return $this
     */
    public function build()
    {
        $orgName = $this->org->orgName;
        //dd($this->array);
        return $this
            ->subject("$orgName Group Registration Confirmation")
            ->view('v1.auth_pages.events.registration.group_receipt')
            ->with([
                'event' => $this->array['event'] , 'quantity' => $this->array['quantity'],
                 'loc' => $this->array['loc'],
                'rf' => $this->rf, 'person' => $this->array['person'], 'org' => $this->array['org'],
            ])->attach($this->attachment);
    }
}
