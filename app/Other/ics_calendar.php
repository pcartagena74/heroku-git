<?php
/**
 * Comment:
 * Created: 4/16/2017
 */

namespace App\Other;

use App\Event;
use App\Location;
use App\Org;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ics_calendar
{
    private $end;
    private $html;
    private $contact;
    private $created;
    private $org;
    private $o_string;
    private $summary;
    private $description;
    private $location;
    private $stamp;
    private $start;
    private $title;
    private $tzid;
    private $uid;
    private $updated;
    private $uri;
    private $event;

    public function __construct(Event $event)
    {
        $etype = DB::table('org-event_types')
            ->where('etID', $event->eventTypeID)
            ->select('etName')
            ->first();
        $org = Org::find($event->orgID);
        $loc = Location::find($event->locationID);

        $this->contact     = $event->contactEmail;
        $this->venue_uid   = $loc->locID . '@mcentric.org';
        $this->title       = trans('messages.mCentric_text.hosted_event', ['org' => $org->orgName]);
        $timezone          = DB::table('timezone')->where('zoneOffset', '=', $event->eventTimeZone)->select('tzid')->first();
        $this->tzid        = $timezone;
        $this->start       = $this->_getUTCDateTime($event->eventStartDate, $timezone->tzid);
        $this->end         = $this->_getUTCDateTime($event->eventEndDate, $timezone->tzid);
        $this->created     = $this->_getUTCDateTime($event->createDate, $timezone->tzid);
        $this->updated     = $this->_getUTCDateTime($event->updateDate, $timezone->tzid);
        $this->html        = str_replace(PHP_EOL, '', $event->eventDescription);
        $this->org         = $org->orgName;
        $this->summary     = trans('messages.email_txt.for_det_visit') . ": " . env('APP_URL') . "/events/" . $event->slug;
        $this->description = $org->orgName . " - " . $event->eventName;
        $this->tzid        = str_replace(" ", "_", $this->tzid->tzid);
        $this->location    = null; // $this->venue_uid . ":" . $loc->locName . " \r\n " . $loc->addr1 . " \r\n " . $loc->addr2  or '' . " \r\n " . $loc->city . ", " . $loc->state . " " . $loc->zip . "\r\n";
        $this->uri         = env('APP_URL') . "/events/" . $event->slug;
        $this->uid         = $event->eventStartDate->format('Ymd\THis') . $event->eventID . "@mcentric.org";
        $this->stamp       = Carbon::now()->setTimezone('UTC')->format('Ymd\THis');
        $this->event       = $event;
        $this->_gen_loc_string($loc);
    }

    private function _getUTCDateTime($date, $timezone)
    {
        $date->setTimezone("UTC");
        return $date;
        // $date = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'America/New_York');
    }
    private function _escapeString($string)
    {
        return wordwrap(preg_replace('/([\,;])/', '\\\$1', ($string) ? $string : ''), 75, "\r\n ", true);
    }

    public function get()
    {
        ($this->o_string) ? $this->o_string : $this->_generate();
        return $this->o_string;
    }

    private function _gen_loc_string(Location $loc)
    {
        $this->location = $this->venue_uid . ":" . $loc->locName . "\r\n " . $loc->addr1 . "\r\n ";
        if ($loc->addr2 !== null) {
            $this->location .= $loc->addr2 . "\r\n ";
        }
        $this->location .= $loc->city . ", " . $loc->state . " " . $loc->zip;
    }

    private function _generate()
    {
        $this->o_string .= "BEGIN:VCALENDAR\r\n" .
        "PRODID:-//" . $this->title . "\r\n" .
        "VERSION:2.0\r\n" .
        "METHOD:REQUEST\r\n" ;
        $this->o_string .= $this->_appendTimezoneSettings() . "\r\n";
        $this->o_string .="BEGIN:VEVENT\r\n" .
        "SUBJECT:" . $this->_escapeString($this->description) . "\r\n" .
        "CLASS:PUBLIC" . "\r\n" .
        "CREATED:" . $this->created->format('Ymd\THis') . "\r\n" .
        "DTSTART;TZID=" . $this->tzid . ":" . $this->start->format('Ymd\THis') . "\r\n" .
        "DTEND;TZID=" . $this->tzid . ":" . $this->end->format('Ymd\THis') . "\r\n" .
        "LOCATION:" . $this->_escapeString($this->location) . "\r\n" .
        "SUMMARY:" . $this->_escapeString($this->description) . "\r\n" .
        "DESCRIPTION:" . $this->_escapeString($this->summary . "\r\n <br> \r\n Information for Registerred Attendees: \r\n " . $this->event->postRegInfo) . "\r\n" .
        "URL;VALUE=URI:" . $this->_escapeString($this->uri) . "\r\n" .
        "UID:" . $this->uid . "\r\n" .
        "SEQUENCE:0\r\n" .
        "TRANSP:OPAQUE" . "\r\n" .
        "DTSTAMP:" . $this->stamp . "\r\n" .
        "LAST-MODIFIED:" . $this->updated->format('Ymd\THis') . "\r\n" .
        "ORGANIZER;CN=" . $this->_escapeString($this->org) . ":MAILTO:" . $this->contact . "\r\n" .
            "X-MICROSOFT-CDO-BUSYSTATUS:Confirmed" . "\r\n" .
            "X-MICROSOFT-CDO-INTENDEDSTATUS:Confirmed" . "\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n";
    }

    private function _appendTimezoneSettings()
    {
        $vtimezone = 'BEGIN:VTIMEZONE
TZID:America/Los_Angeles
X-LIC-LOCATION:America/Los_Angeles
BEGIN:DAYLIGHT
TZOFFSETFROM:-0800
TZOFFSETTO:-0700
TZNAME:PDT
DTSTART:19700308T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:-0700
TZOFFSETTO:-0800
TZNAME:PST
DTSTART:19701101T020000
RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU
END:STANDARD
END:VTIMEZONE
BEGIN:VTIMEZONE
TZID:America/Phoenix
X-LIC-LOCATION:America/Phoenix
BEGIN:STANDARD
TZOFFSETFROM:-0700
TZOFFSETTO:-0700
TZNAME:MST
DTSTART:19700101T000000
END:STANDARD
END:VTIMEZONE
BEGIN:VTIMEZONE
TZID:America/Chicago
X-LIC-LOCATION:America/Chicago
BEGIN:DAYLIGHT
TZOFFSETFROM:-0600
TZOFFSETTO:-0500
TZNAME:CDT
DTSTART:19700308T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:-0500
TZOFFSETTO:-0600
TZNAME:CST
DTSTART:19701101T020000
RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU
END:STANDARD
END:VTIMEZONE
BEGIN:VTIMEZONE
TZID:America/New_York
X-LIC-LOCATION:America/New_York
BEGIN:DAYLIGHT
TZOFFSETFROM:-0500
TZOFFSETTO:-0400
TZNAME:EDT
DTSTART:19700308T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:-0400
TZOFFSETTO:-0500
TZNAME:EST
DTSTART:19701101T020000
RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU
END:STANDARD
END:VTIMEZONE';
        return $vtimezone;
    }

    /*

"X-ALT-DESC;FMTTYPE=text/html:".$this->_escapeString($this->html)."\r\n" . $this->uri . "\n".

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
