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

    /**
     * @param EventSession $es
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Laravel\Lumen\Http\Redirector
     * @throws \Exception
     *
     * Sessions get destroyed when removed via the session editing screen
     */
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

    public function update(EventSession $es){
        $event = Event::find($es->eventID);
        $es->deleted_at = null;
        $es->updaterID = auth()->user()->id;
        $es->save();
        return redirect('/tracks/' . $event->eventID);
    }
}
