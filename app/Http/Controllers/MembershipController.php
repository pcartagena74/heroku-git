<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function (Request $request, $next) {
            if (auth()) {
                $this->currentPerson = Person::find(auth()->user()->id);
            }

            return $next($request);
        });
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function index($which = 'new', $days = 90, $page = 25): View
    {
        $orgID = $this->currentPerson->defaultOrgID;
        [$members, $title] = membership_reports($orgID, $which, 0, $days, $page);

        return view('v1.auth_pages.members.new_or_expiring',
            compact('members', 'title', 'orgID', 'which', 'days'));
    }
}
