<?php

namespace App\Http\Controllers;

use App\EmailList;
use App\Event;
use App\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmailListController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->currentPerson = Person::find(auth()->id());
        $rows                = [];
        $ids                 = [];
        $defaults            = EmailList::where('orgID', 1)->get();
        $lists               = EmailList::where('orgID', $this->currentPerson->defaultOrgID)->get();
        $today               = Carbon::now();

        foreach ($defaults as $l) {
            if ($l->foundation == 'everyone') {
                $c = Person::whereHas('orgs', function ($q) {
                    $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                })
                    ->count();
            } elseif ($l->foundation == 'pmiid') {
                $c = Person::whereHas('orgs', function ($q) {
                    $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                })
                    ->whereHas('orgperson', function ($q) {
                        $q->whereNotNull('OrgStat1');
                    })
                    ->count();
            } elseif ($l->foundation == 'nonexpired') {
                $c = Person::whereHas('orgs', function ($q) {
                    $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                })
                    ->whereHas('orgperson', function ($q) {
                        $q->whereDate('RelDate4', '>=', Carbon::now());
                    })
                    ->count();
            } else {
                // For default lists, we shouldn't ever get here
                $c = 0;
            }
            array_push($rows, [$l->listName, $c, $l->created_at->format('n/j/Y')]);
        }
        $defaults = $rows;
        $rows     = [];

        foreach ($lists as $l) {
            $included   = explode(',', $l->included);
            $foundation = $l->foundation;
            $excluded   = explode(',', $l->excluded);

            // foundations are either filters (when $included !== null) or true foundations
            if ($included != null) {
                switch ($foundation) {
                    case "none":
                    case "everyone":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($included, $excluded) {
                                $q->whereIn('eventID', $included);
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "pmiid":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($included, $excluded) {
                                $q->whereIn('eventID', $included);
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) {
                                $q->whereNotNull('OrgStat1');
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "nonexpired":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($included, $excluded) {
                                $q->whereIn('eventID', $included);
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) use ($today) {
                                $q->whereNotNull('OrgStat1');
                                $q->whereDate('RelDate4', '>=', $today);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                }
            } else {
                // $included === null
                switch ($foundation) {
                    case "none":
                    // none with a null $included is not possible
                    case "everyone":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereDoesntHave('registrations', function ($q) use ($excluded) {
                                $q->whereIn('eventID', $excluded);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "pmiid":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($excluded) {
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) use ($today) {
                                $q->whereNotNull('OrgStat1');
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "nonexpired":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($excluded) {
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) use ($today) {
                                $q->whereNotNull('OrgStat1');
                                $q->whereDate('RelDate4', '>=', $today);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                }
            }

            array_push($rows, [$l->listName, $l->listDesc, $c, $l->created_at->format('n/j/Y')]);
        }
        $lists = $rows;

        // list of eventIDs from this year's events
        $e = Event::whereYear('eventStartDate', '=', date('Y'))
            ->whereDate('eventStartDate', '<', $today)
            ->select('eventID')
            ->get();
        foreach ($e as $id) {
            array_push($ids, $id->eventID);
        }
        $ytd_events = implode(',', $ids);
        $ids        = [];

        // list of eventIDs from last year's events
        $e = Event::whereYear('eventStartDate', '=', date('Y') - 1)
            ->select('eventID')
            ->get();
        foreach ($e as $id) {
            array_push($ids, $id->eventID);
        }
        $last_year = implode(',', $ids);
        $ids       = [];

        $e = Event::where('eventTypeID', '=', 3)->select('eventID')->get();
        foreach ($e as $id) {
            array_push($ids, $id->eventID);
        }
        $pddays = implode(',', $ids);

        $excludes = Event::whereYear('eventStartDate', '=', date('Y'))->get();

        return view('v1.auth_pages.campaigns.email_lists', compact('defaults', 'lists', 'ytd_events', 'last_year', 'pddays', 'excludes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @params:
     *        $foundation: none, everyone, pmiid, nonexpired
     *        $include: empty or array of comma-separated eventID lists
     *        $exclude: empty or array of eventIDs
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // responds to POST /list
        $this->currentPerson = Person::find(auth()->id());
        $include_string      = '';
        $exclude_string      = '';

        $name        = request()->input('name');
        $description = request()->input('description');
        $foundation  = request()->input('foundation');
        $include     = request()->input('include');
        $exclude     = request()->input('exclude');

        $include !== null ? $include_string = $foundation . "," . implode(',', $include) :
        $include_string                     = $foundation;
        $exclude !== null ? $exclude_string = implode(',', $exclude) : $exclude_string = null;

        if ($include === null && $foundation == 'none') {
            request()->session()->flash('alert-warning', "You need to choose a foundation or events to include.");
            return redirect()->back();
        }

        $e           = new EmailList;
        $e->orgID    = $this->currentPerson->defaultOrgID;
        $e->listName = $name;
        $e->listDesc = $description;
        $e->included = $include_string;
        $e->excluded = $exclude_string;
        $e->save();

        return redirect(env('APP_URL') . '/lists');
    }

    /**
     * Display the specified resource and process index()
     * as well, redirecting to appropriate tab
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(EmailList $emailList)
    {
        $this->currentPerson = Person::find(auth()->id());
        $rows                = [];
        $ids                 = [];
        $defaults            = EmailList::where('orgID', 1)->get();
        $lists               = EmailList::where('orgID', $this->currentPerson->defaultOrgID)->get();
        $today               = Carbon::now();

        foreach ($defaults as $l) {
            if ($l->included == 'everyone') {
                $c = Person::whereHas('orgs', function ($q) {
                    $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                })
                    ->count();
            } elseif ($l->included == 'pmiid') {
                $c = Person::whereHas('orgs', function ($q) {
                    $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                })
                    ->whereHas('orgperson', function ($q) {
                        $q->whereNotNull('OrgStat1');
                    })
                    ->count();
            } elseif ($l->included == 'nonexpired') {
                $c = Person::whereHas('orgs', function ($q) {
                    $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                })
                    ->whereHas('orgperson', function ($q) {
                        $q->whereDate('RelDate4', '>=', Carbon::now());
                    })
                    ->count();
            } else {
                $c = 0;
            }
            array_push($rows, [$l->listName, $c, $l->created_at->format('n/j/Y')]);
        }
        $defaults = $rows;
        $rows     = [];

        foreach ($lists as $l) {
            $included   = explode(',', $l->included);
            $foundation = array_shift($included);
            $excluded   = explode(',', $l->excluded);

            /*
            $c = Person::whereHas('orgs', function($q) {
            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
            })
            ->whereHas('registrations', function($q) use ($included, $excluded) {
            $q->whereIn('eventID', $included);
            $q->whereNotIn('eventID', $excluded);
            })
            ->distinct()
            ->select('person.personID')
            ->count();
            dd($c);
             */
            // foundations are either filters (when $included !== null) or true foundations
            if ($included != null) {
                switch ($foundation) {
                    case "none":
                    case "everyone":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($included, $excluded) {
                                $q->whereIn('eventID', $included);
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "pmiid":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($included, $excluded) {
                                $q->whereIn('eventID', $included);
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) {
                                $q->whereNotNull('OrgStat1');
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "nonexpired":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($included, $excluded) {
                                $q->whereIn('eventID', $included);
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) use ($today) {
                                $q->whereNotNull('OrgStat1');
                                $q->whereDate('RelDate4', '>=', $today);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                }
            } else {
                // $included === null
                switch ($foundation) {
                    case "none":
                    // none with a null $included is not possible
                    case "everyone":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereDoesntHave('registrations', function ($q) use ($excluded) {
                                $q->whereIn('eventID', $excluded);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "pmiid":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($excluded) {
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) use ($today) {
                                $q->whereNotNull('OrgStat1');
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                    case "nonexpired":
                        $c = Person::whereHas('orgs', function ($q) {
                            $q->where('organization.orgID', $this->currentPerson->defaultOrgID);
                        })
                            ->whereHas('registrations', function ($q) use ($excluded) {
                                $q->whereNotIn('eventID', $excluded);
                            })
                            ->whereHas('orgperson', function ($q) use ($today) {
                                $q->whereNotNull('OrgStat1');
                                $q->whereDate('RelDate4', '>=', $today);
                            })
                            ->distinct()
                            ->select('person.personID')
                            ->count();
                        break;
                }
            }

            array_push($rows, [$l->listName, $l->listDesc, $c, $l->created_at->format('n/j/Y')]);
        }
        $lists = $rows;

        // list of eventIDs from this year's events
        $e = Event::whereYear('eventStartDate', '=', date('Y'))
            ->whereDate('eventStartDate', '<', $today)
            ->select('eventID')
            ->get();
        foreach ($e as $id) {
            array_push($ids, $id->eventID);
        }
        $ytd_events = implode(',', $ids);
        $ids        = [];

        // list of eventIDs from last year's events
        $e = Event::whereYear('eventStartDate', '=', date('Y') - 1)
            ->select('eventID')
            ->get();
        foreach ($e as $id) {
            array_push($ids, $id->eventID);
        }
        $last_year = implode(',', $ids);
        $ids       = [];

        $e = Event::where('eventTypeID', '=', 3)->select('eventID')->get();
        foreach ($e as $id) {
            array_push($ids, $id->eventID);
        }
        $pddays = implode(',', $ids);

        $excludes = Event::whereYear('eventStartDate', '=', date('Y'))->get();

        return view(
            'v1.auth_pages.campaigns.email_lists',
            compact('defaults', 'lists', 'ytd_events', 'last_year', 'pddays', 'excludes', 'emailList')
        )
            ->withInput(['tab' => 'tab_content2']);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
