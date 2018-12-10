<?php

namespace App\Http\Controllers;

use App\OrgPerson;
use App\Org;
use App\Person;
use Illuminate\Http\Request;

class OrgPersonController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('guest');
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Org $org)
    {
        $person = null;
        return view('v1.public_pages.pmiID_lookup', compact('org', 'person'));
    }

    public function find(Request $request) {
        $orgID = $request->input('orgID');
        $pmiID = $request->input('pmiID');

        $who = OrgPerson::where([
            ['OrgStat1', '=', $pmiID],
            ['orgID', '=', $orgID]
            ])->with('myperson')->first();

        if($who){
            return redirect(env('APP_URL')."/pmi_account/".$who->myperson->personID);
        } else {
            request()->session()->flash('alert-danger', trans('messages.instructions.pmiID_not_found', ['pmiID' => $pmiID]));
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OrgPerson  $orgperson
     * @return \Illuminate\Http\Response
     */
    public function show(Person $person)
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OrgPerson  $orgperson
     * @return \Illuminate\Http\Response
     */
    public function edit(OrgPerson $orgperson)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OrgPerson  $orgperson
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrgPerson $orgperson)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OrgPerson  $orgperson
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrgPerson $orgperson)
    {
        //
    }
}
