<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSession;
use App\Models\Track;
use Illuminate\Http\Request;

class EventSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * @param EventSession $es
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $es = EventSession::withTrashed()->find($id);
        $function = request()->input('function');
        $event = Event::find($es->eventID);
        $updaterID = auth()->user()->id;

        switch ($function) {
            case 'restore_session':
                if (null !== $es) {
                    $es->deleted_at = null;
                    $es->updaterID = $updaterID;
                    $es->save();
                }
                break;
            case 'restore_row':
                $order = request()->input('order');
                $confDay = request()->input('confDay');
                $row = EventSession::where([
                    ['eventID', $es->eventID],
                    ['confDay', $confDay],
                    ['order', $order],
                ])->withTrashed()->get();

                foreach ($row as $es) {
                    $es->deleted_at = null;
                    $es->updaterID = $updaterID;
                    $es->save();
                }
                break;
        }

        return redirect('/tracks/'.$event->eventID);
    }

    /**
     * @param EventSession $es
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
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

        return redirect('/tracks/'.$event->eventID);
    }
}
