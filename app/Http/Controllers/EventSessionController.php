<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\EventSession;
use App\Event;
use App\Track;

class EventSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function destroy(EventSession $es)
    {
        $event = Event::find($es->eventID);

        if ($event->isSymmetric) {
            $sessions = EventSession::where([
                ['order', $es->order],
                ['confDay', '=', $es->confDay],
                ['eventID', $event->eventID],
            ])->get();

            foreach ($sessions as $os) {
                $os->delete();
            }
        } else {
            $es->delete();
        }

        return redirect('/tracks/' . $event->eventID);
    }
}
