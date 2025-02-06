<?php

namespace App\Http\Controllers;

use App\Models\Org;
use App\Models\OrgPerson;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrgPersonController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('guest');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Org $org): View
    {
        $person = null;

        return view('v1.public_pages.pmiID_lookup', compact('org', 'person'));
    }

    public function find(Request $request): RedirectResponse
    {
        $orgID = $request->input('orgID');
        $pmiID = $request->input('pmiID');
        $email = $request->input('email');
        $who = null;

        if ($pmiID > 0) {
            $who = OrgPerson::where([
                ['OrgStat1', '=', $pmiID],
                ['orgID', '=', $orgID],
            ])->with('myperson')->first();
        } elseif ($email !== null) {
            $who = Person::whereHas('emails', function ($q) use ($email) {
                $q->where('emailADDR', '=', "$email");
            })->first();
        }

        if ($who !== null && $who->myperson !== null && $pmiID) {
            return redirect(env('APP_URL').'/pmi_account/'.$who->myperson->personID);
        } elseif ($who !== null && $who->myperson !== null && $email) {
            return redirect(env('APP_URL').'/pmi_account/'.$who->personID);
        } else {
            if ($pmiID > 0) {
                request()->session()->flash('alert-danger', trans_choice('messages.instructions.pmiID_not_found', $pmiID, ['pmiID' => $pmiID]));
            } else {
                request()->session()->flash('alert-danger', trans_choice('messages.instructions.pmiID_not_found', 0, ['email' => $email]));
            }

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OrgPerson  $orgperson
     */
    public function show(Person $person): View
    {
        $person = $person->load('emails', 'user', 'orgperson');
        $org = Org::find($person->defaultOrgID);

        return view('v1.public_pages.pmiID_lookup', compact('org', 'person'));
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(OrgPerson $orgperson)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrgPerson $orgperson)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrgPerson $orgperson)
    {
        //
    }
}
