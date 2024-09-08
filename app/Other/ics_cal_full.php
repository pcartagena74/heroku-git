<?php
/**
 * Comment: PMI MassBay-specific output of complete event listing
 * Created: 4/16/2017
 */

namespace App\Other;

use App\Models\Location;
use App\Models\Org;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ics_cal_full
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

    private $locale;

    private $stamp;

    private $start;

    private $subject;

    private $title;

    private $tzid;

    private $uid;

    private $updated;

    private $uri;

    private $v_string;

    private $venue_uid;

    public function __construct(Collection $events)
    {
        $this->events = $events;
        $this->org = Org::find($events->first()->orgID);
        $this->locale = App::getLocale();
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

    public function open()
    {
        return "BEGIN:VCALENDAR\r\n".
        "VERSION:2.0\r\n".
        'PRODID:-//'.$this->title."\r\n".
            "METHOD:REQUEST\r\n";
    }

    public function close()
    {
        return "END:VCALENDAR\r\n";
    }

    private function _gen_venue(Location $l)
    {
        if ($l->isVirtual) {
            $this->v_string =
            "BEGIN:VVENUE\r\n".
            'UID:'.$this->venue_uid."\r\n".
            'NAME:'.$l->locName."\r\n".
                "END:VVENUE\r\n";
        } else {
            $cntry = DB::table('countries')->where('cntryID', '=', $l->countryID)->first();
            $state = DB::table('state')->where('abbrev', '=', $l->state)->first();

            $this->v_string =
            "BEGIN:VVENUE\r\n".
            'UID:'.$this->venue_uid."\r\n".
            'NAME:'.$l->locName."\r\n".
            'STREET-ADDRESS:'.$l->addr1."\r\n";
            if ($l->addr2) {
                $this->v_string .= 'EXTENDED-ADDRESS:'.$l->addr2."\r\n";
            }
            $this->v_string .=
            'LOCALITY:'.$l->city."\r\n".
            'REGION;ABBREV:'.$l->state.':'.$state->stateName."\r\n".
            'COUNTRY;ABBREV:'.$cntry->cntryCode.':'.$cntry->cntryName."\r\n".
            'POSTAL-CODE:'.$l->zip."\r\n".
            'URL;TYPE=Map:https://maps.google.it/maps?q='.
            urlencode($l->addr1.' '.$l->addr2.' '.$l->city.', '.$l->state.' '.$l->zip)."&hl=$this->locale\r\n".
                "END:VVENUE\r\n";
        }

        return $this->v_string;
    }

    private function _gen_loc_string(Location $loc)
    {
        $this->location = $this->venue_uid.':'.$loc->locName."\n ".$loc->addr1."\n ";
        if ($loc->addr2 !== null) {
            $this->location .= $loc->addr2."\n ";
        }
        $this->location .= $loc->city.', '.$loc->state.' '.$loc->zip;
    }

    private function _generate()
    {
        $this->o_string = '';
        $this->v_string = '';

        foreach ($this->events as $event) {
            $loc = Location::find($event->locationID);
            $org = Org::find($event->orgID);
            $etype = DB::table('org-event_types')
                ->where('etID', $event->eventTypeID)
                ->select('etName')
                ->first();

            $this->contact = $event->contactEmail;
            $this->venue_uid = $loc->locID.'@mcentric.org';
            $this->title = trans('messages.mCentric_text.hosted_event', ['org' => $org->orgName]);
            $this->start = $event->eventStartDate;
            $this->end = $event->eventEndDate;
            $this->created = $event->createDate;
            $this->updated = $event->updateDate;
            $this->html = str_replace(PHP_EOL, '', $event->eventDescription);
            //$this->summary     = trans('messages.email_txt.for_det_visit') . ": " . env('APP_URL') . "/events/" . $event->slug;
            $this->subject = $event->eventName;
            $this->summary = $this->subject;
            $this->categories = $etype->etName;
            $this->description = $this->html;
            $this->tzid = DB::table('timezone')->where('zoneOffset', '=', $event->eventTimeZone)->select('tzid')->first();
            $this->tzid = str_replace(' ', '_', $this->tzid->tzid);
            $this->uri = env('APP_URL').'/events/'.$event->slug;
            $this->uid = $event->eventStartDate->format('Ymd\THis').$event->eventID.'@mcentric.org';
            $this->stamp = Carbon::now()->format('Ymd\THis');
            //$this->location = $this->venue_uid . ":" . $loc->locName . " \r\n " . $loc->addr1 . " \r\n " . $loc->addr2 . " \r\n " . $loc->city . ", " . $loc->state . " " . $loc->zip . "\r\n";
            $this->_gen_loc_string($loc);

            $this->o_string .= $this->_appendTimezoneSettings()."\r\n".
            "BEGIN:VEVENT\r\n".
            'SUBJECT:'.$this->_escapeString($this->subject)."\r\n".
            'SUMMARY:'.$this->_escapeString($this->summary)."\r\n".
            'UID:'.$this->uid."\r\n".
            "SEQUENCE:0\r\n".
            'CLASS:PUBLIC'."\r\n".
            'CREATED:'.$this->created->format('Ymd\THis')."\r\n".
            'DTSTART;TZID='.$this->tzid.':'.$this->start->format('Ymd\THis')."\r\n".
            'DTEND;TZID='.$this->tzid.':'.$this->end->format('Ymd\THis')."\r\n".
            'CATEGORIES:'.$this->_escapeString($this->categories)."\r\n".
            'LOCATION;VVENUE='.$this->_escapeString($this->location)."\r\n".
            'URL;VALUE=URI:'.$this->_escapeString($this->uri)."\r\n".
            'TRANSP:OPAQUE'."\r\n".
            'DTSTAMP:'.$this->stamp."\r\n".
            'LAST-MODIFIED:'.$this->updated->format('Ymd\THis')."\r\n".
            'ORGANIZER;CN='.$this->_escapeString($this->org->orgName).':MAILTO:'.$this->contact."\r\n".
            'X-MICROSOFT-CDO-BUSYSTATUS:Confirmed'."\r\n".
            'X-MICROSOFT-CDO-INTENDEDSTATUS:Confirmed'."\r\n".
            'DESCRIPTION:'.$this->_escapeString($this->description)."\r\n".
                "END:VEVENT\r\n";

            //$this->o_string .= $this->_gen_venue($loc);
        }
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
}
