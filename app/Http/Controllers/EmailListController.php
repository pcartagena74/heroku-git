<?php

namespace App\Http\Controllers;

use App\EmailList;
use App\Event;
use App\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

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
        $validator           = Validator::make($request->all(), [
            'name'        => 'required|max:255',
            'description' => 'nullable|min:3',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors_validation' => $validator->errors()]);
        }

        $name         = request()->input('name');
        $description  = request()->input('description');
        $foundation   = request()->input('foundation');
        $include      = request()->input('include');
        $exclude      = request()->input('exclude');
        $year_date    = $request->input('eventStartDate');
        $include_list = [];
        $exclude_list = [];

        $has_this_year   = false;
        $current_year_in = '';
        if (!empty($include)) {
            foreach ($include as $event_id) {
                if (strpos($event_id, 'current-year#') === 0) {
                    $date       = explode('-', $year_date);
                    $from       = date('Y-m-d', strtotime($date[0]));
                    $to         = date('Y-m-d', strtotime($date[1]));
                    $event_list = Event::whereBetween('eventStartDate', [$from, $to])->select('eventID')->get()->toArray();
                    foreach ($event_list as $key => $value) {
                        $include_list[$value['eventID']] = $value['eventID'];
                    }
                    // $has_this_year = 'include';
                    // $list          = str_replace('current-year#', '', $event_id);
                    // $list          = array_flip(explode(',', $list));
                    // $include_list  = array_replace($include_list, $list);
                } else if (strpos($event_id, 'last-year#') === 0) {
                    $list         = str_replace('last-year#', '', $event_id);
                    $list         = array_flip(explode(',', $list));
                    $include_list = array_replace($include_list, $list);
                } else {
                    $include_list[$event_id] = $event_id;
                }
            }
        }
        if (!empty($exclude)) {
            foreach ($exclude as $event_id) {
                if (strpos($event_id, 'current-year#') === 0) {
                    $date       = explode('-', $year_date);
                    $from       = date('Y-m-d', strtotime($date[0]));
                    $to         = date('Y-m-d', strtotime($date[1]));
                    $event_list = Event::whereBetween('eventStartDate', [$from, $to])->select('eventID')->get()->toArray();
                    foreach ($event_list as $key => $value) {
                        $exclude_list[$value['eventID']] = $value['eventID'];
                    }
                } else if (strpos($event_id, 'last-year#') === 0) {
                    $list         = str_replace('last-year#', '', $event_id);
                    $list         = array_flip(explode(',', $list));
                    $exclude_list = array_replace($exclude_list, $list);
                } else {
                    $exclude_list[$event_id] = $event_id;
                }
            }
        }
        // if ($has_this_year != false) {
        //     $date       = explode('-', $year_date);
        //     $from       = date('Y-m-d', strtotime($date[0]));
        //     $to         = date('Y-m-d', strtotime($date[1]));
        //     $event_list = Event::whereBetween('eventStartDate', [$from, $to])->select('eventID')->get()->toArray();
        //     foreach ($event_list as $key => $value) {
        //         if ($has_this_year == 'include') {
        //             $include_list[$value['eventID']] = $value['eventID'];
        //         } else if ($has_this_year == 'exclude') {
        //             $exclude_list[$value['eventID']] = $value['eventID'];
        //         }
        //     }
        // }

        // dd($foundation, $include_list, $exclude_list);
        if ($foundation == 'none' && empty($include_list) && empty($exclude_list)) {
            return response()->json(['success' => false, 'errors' => ['gen' => trans('messages.errors.no_member_for_list')]]);
        }
        if ($foundation == 'none' && empty($include_list)) {
            // request()->session()->flash('alert-warning', "You need to choose a foundation or events to include.");
            return response()->json(['success' => false, 'errors' => ['gen' => trans('messages.errors.no_foundation_or_include')]]);
        }

        if (empty($include_list) && $foundation) {
            $include_string = $foundation;
        }
        $exclude !== null ? $exclude_string = implode(',', $exclude_list) : $exclude_string = null;

        /* start show result before save */
        //ask phil if we need to check in advance that if a list has some contact or not
        /* end show result before save */

        $e           = new EmailList;
        $e->orgID    = $this->currentPerson->defaultOrgID;
        $e->listName = $name;
        $e->listDesc = $description;
        $e->included = $include_string;
        $e->excluded = $exclude_string;
        $e->save();
        request()->session()->flash('alert-success', trans('messages.messages.email_list_created', ['name' => $name]));
        return response()->json(['success' => true, 'redirect_url' => url('lists')]);
        // return redirect(env('APP_URL') . '/lists');
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
