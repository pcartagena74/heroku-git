<?php
/**
 * Comment: PMI MassBay-specific output of complete event listing
 * Created: 4/16/2017
 */

namespace App\Other;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Org;
use App\Location;
use Illuminate\Database\Eloquent\Collection as Collection;

class ics_cal_full
{
    private $title;
    private $start;
    private $end;
    private $created;
    private $updated;
    private $html;
    private $summary;
    private $description;
    private $location;
    private $uri;
    private $tzid;
    private $uid;
    private $stamp;
    private $contact;
    private $org;
    private $o_string;

    public function __construct(Collection $events) {

        $this->events = $events;
        $this->org = Org::find($events->first()->orgID);
    }

    private function _escapeString($string)
    {
        return preg_replace('/([\,;])/', '\\\$1', ($string) ? $string : '');
    }

    public function get()
    {
        ($this->o_string) ? $this->o_string : $this->_generate();
        return $this->o_string;
    }

    public function open(){
        return "BEGIN:VCALENDAR\r\n".
               "VERSION:2.0\r\n".
               "PRODID:-//" . $this->title . "\r\n".
               "METHOD:REQUEST\r\n";
    }

    public function close(){
        return "END:VCALENDAR\r\n";
    }

    private function _generate()
    {

        $this->o_string = '';
        foreach($this->events as $event){
            $loc               = Location::find($event->locationID);
            $org               = Org::find($event->orgID);
            $etype             = DB::table('org-event_types')
                                    ->where('etID', $event->eventTypeID)
                                    ->select('etName')
                                    ->first();

            $this->title       = trans('messages.mCentric_text.hosted_event', $org->orgName);
            $this->start       = $event->eventStartDate;
            $this->end         = $event->eventEndDate;
            $this->created     = $event->createDate;
            $this->updated     = $event->updateDate;
            $this->html        = wordwrap($event->eventDescription, 75, "\r\n ", true);
            //$this->summary     = trans('messages.email_txt.for_det_visit') . ": " . env('APP_URL') . "/events/" . $event->slug;
            $this->subject     = $event->eventName;
            $this->summary     = $this->subject;
            $this->categories  = $etype->etName;
            $this->description = $this->html;
            $this->tzid        = DB::table('timezone')->where('zoneOffset', '=', $event->eventTimeZone)->select('tzid')->first();
            $this->tzid        = str_replace(" ", "_", $this->tzid->tzid);
            $this->location    = $this->contact . ":" . $loc->locName . "\r\n " . $loc->addr1 . "\r\n " . $loc->addr2 . "\r\n " . $loc->city . ", " . $loc->state . " " . $loc->zip . "\r\n ";
            $this->uri         = env('APP_URL') . "/events/" . $event->slug;
            $this->uid         = $event->eventStartDate->format('Ymd\THis') . $event->eventID . "@mcentric.org";
            $this->stamp       = Carbon::now()->format('Ymd\THis');
            $this->contact     = $event->contactEmail;

            $this->o_string .=
                "BEGIN:VEVENT\r\n".
                "SUBJECT:".$this->_escapeString($this->subject)."\r\n".
                "SUMMARY:".$this->_escapeString($this->summary)."\r\n".
                "UID:". $this->uid ."\r\n".
                "SEQUENCE:0\r\n".
                "CLASS:PUBLIC"."\r\n".
                "CREATED:".$this->created->format('Ymd\THis')."\r\n".
                "DTSTART;TZID=" . $this->tzid . ":".$this->start->format('Ymd\THis')."\r\n".
                "DTEND;TZID=" . $this->tzid . ":".$this->end->format('Ymd\THis')."\r\n".
                "CATEGORIES:".$this->_escapeString($this->categories)."\r\n".
                "LOCATION;VVENUE=".$this->_escapeString($this->location)."\r\n".
                "URL;VALUE=URI:".$this->_escapeString($this->uri)."\r\n".
                "TRANSP:OPAQUE"."\r\n".
                "DTSTAMP:". $this->stamp ."\r\n".
                "LAST-MODIFIED:" . $this->updated->format('Ymd\THis') . "\r\n".
                "ORGANIZER;CN=".$this->_escapeString($this->org->orgName).":MAILTO:" . $this->contact . "\r\n".
                "X-MICROSOFT-CDO-BUSYSTATUS:Confirmed"."\r\n".
                "X-MICROSOFT-CDO-INTENDEDSTATUS:Confirmed"."\r\n".
                "DESCRIPTION:".$this->_escapeString($this->description)."\r\n".
                "END:VEVENT\r\n";
        }
    }
}
