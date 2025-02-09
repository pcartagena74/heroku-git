<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use App\Models\Person;
use Illuminate\Http\Request;

class EventTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // responds to POST to /eventtype/create and creates, adds, stores the event
        $this->currentPerson = Person::find(auth()->user()->id);
        $orgID = $this->currentPerson->defaultOrgID;

        for ($i = 1; $i <= 5; $i++) {
            $eventType = 'eventType-'.$i;

            $type = request()->input($eventType);
            if (isset($type)) {
                $newET = new EventType;
                $newET->orgID = $orgID;
                $newET->creatorID = $this->currentPerson->personID;
                $newET->etName = $type;
                $newET->save();
            }
        }

        return redirect('/eventdefaults');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(EventType $eventType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(EventType $eventType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // responds to POST /eventType/id
        $etID = request()->input('pk');

        $name = request()->input('name');
        if (strpos($name, '-')) {
            // if passed from the registration receipt, the $name will have a dash
            [$name, $field] = array_pad(explode('-', $name, 2), 2, null);
        }
        $value = request()->input('value');

        $oet = EventType::find($etID);
        $oet->{$name} = $value;
        $oet->updaterID = auth()->user()->id;
        $oet->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // responds to DELETE /eventtype/id/delete
        $this->currentPerson = Person::find(auth()->user()->id);
        $et = EventType::find($id);
        $et->updaterID = $this->currentPerson->personID;
        $et->save();
        $et->delete();

        return redirect('/eventdefaults');
    }
}
