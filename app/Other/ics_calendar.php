<?php
/**
 * Comment:
 * Created: 4/16/2017
 */

namespace App\Other;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Org;
use App\Location;


class ics_calendar
{
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

    public function __construct (\App\Event $event) {
        $etype             = DB::table('org-event_types')
                               ->where('etID', $event->eventTypeID)
                               ->select('etName')
                               ->first();
        $org               = Org::find($event->orgID);
        $loc               = Location::find($event->locationID);

        $this->start       = $event->eventStartDate;
        $this->end         = $event->eventEndDate;
        $this->created     = $event->createDate;
        $this->updated     = $event->updateDate;
        $this->html        = $event->eventDescription;
        $this->org         = $org->orgName;
        $this->summary     = "For details, visit: " . env('APP_URL') . "/events/" . $event->slug;
        $this->description = $org->orgName . " " . $etype->etName;
        $this->tzid        = DB::table('timezone')->where('zoneOffset', '=', $event->eventTimeZone)->select('tzid')->first();
        $this->tzid        = $this->tzid->tzid;
        $this->location    = $loc->locName . " " . $loc->addr1 . ", " . $loc->addr2 . ", " . $loc->city . ", " . $loc->state . " " . $loc->zip;
        $this->uri         = env('APP_URL') . "/events/" . $event->slug;
        $this->uid         = $event->eventStartDate->format('Ymd\THis') . $event->eventID . "@mcentric.org";
        $this->stamp       = Carbon::now()->format('Ymd\THis');
        $this->contact     = $event->contactEmail;
    }

    private function _escapeString($string) {
        return preg_replace('/([\,;])/','\\\$1', ($string) ? $string : '');
    }

    public function get() {
        ($this->o_string) ? $this->o_string : $this->_generate();
        return $this->o_string;
    }

    private function _generate() {

        $this->o_string = "BEGIN:VCALENDAR\n".
            "PRODID:-//mCentric-Hosted Event" ."\n".
            "VERSION:2.0\n".
            "METHOD:REQUEST\n".
            "BEGIN:VEVENT\n".
            "SUBJECT:".$this->_escapeString($this->description)."\n".
            "CLASS:PUBLIC"."\n".
            "CREATED:".$this->created->format('Ymd\THis')."\n".
            "DTSTART;TZID=" . $this->tzid . ":".$this->start->format('Ymd\THis')."\n".
            "DTEND;TZID=" . $this->tzid . ":".$this->end->format('Ymd\THis')."\n".
            "LOCATION:".$this->_escapeString($this->location)."\n".
            "SUMMARY:".$this->_escapeString($this->description)."\n".
            "DESCRIPTION:".$this->_escapeString($this->summary)."\n".
            "URL;VALUE=URI:".$this->_escapeString($this->uri)."\n".
            "UID:". $this->uid ."\n".
            "SEQUENCE:0\n".
            "TRANSP:OPAQUE"."\n".
            "DTSTAMP:". $this->stamp ."\n".
            "LAST-MODIFIED:" . $this->updated->format('Ymd\THis') . "\n".
            "ORGANIZER;CN=".$this->_escapeString($this->org).":mailto:" . $this->contact . "\n".
            "X-MICROSOFT-CDO-BUSYSTATUS:Confirmed"."\n".
            "X-MICROSOFT-CDO-INTENDEDSTATUS:Confirmed"."\n".
            "END:VEVENT\n".
            "END:VCALENDAR\n";
    }
    /*

            "X-ALT-DESC;FMTTYPE=text/html:".$this->_escapeString($this->html)."\n" . $this->uri . "\n".

BEGIN:VCALENDAR
PRODID:-//mCentric-Hosted Events
VERSION:2.0
METHOD:REQUEST
X-MS-OLK-FORCEINSPECTOROPEN:TRUE
BEGIN:VEVENT
ATTENDEE;CN=Name;RSVP=TRUE:mailto:{{ $event->contactEmail }}
CLASS:PUBLIC
CREATED:{{ $event->createDate->format('Ymd\THis') }}
DESCRIPTION:{{ $org->orgName }} {{ $event->etName }}
DTSTART;TZID={{ $tzid }}:{{ $event->eventStartDate->format('Ymd\THis') }}
DTEND;TZID={{ $tzid }}:{{ $event->eventEndDate->format('Ymd\THis') }}
DTSTAMP:{{ \Carbon\Carbon::now()->format('Ymd\THis') }}
LAST-MODIFIED:{{ $event->updateDate->format('Ymd\THis') }}
ORGANIZER;CN=name:mailto:{{ $event->contactEmail }}
PRIORITY:5
SEQUENCE:0
LOCATION:{{ $loc->locName }} {{ $loc->addr1 }} {{ $loc->addr2 }} {{ $loc->city }}, {{ $loc->state }} {{ $loc->zip }}
SUMMARY:{{ $event_url }}
TRANSP:OPAQUE
UID:{{ $event->eventStartDate->format('Ymd\THis') }}@mcentric.org
X-MICROSOFT-CDO-BUSYSTATUS:Confirmed
X-MICROSOFT-CDO-INTENDEDSTATUS:Confirmed
END:VEVENT
END:VCALENDAR

     */

}