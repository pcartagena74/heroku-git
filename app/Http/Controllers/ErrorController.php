<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Models\Person;
use App\Models\Ticketit\TicketOver as Ticket;
use App\Rules\GoogleCaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kordy\Ticketit\Models\Setting;
use Validator;

class ErrorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Show the application dashboard.
     */
    public function index(): View
    {
        return view('v1.public_pages.home-login');
    }

    public function store(Request $request)
    {
        // This is the function that processes issues reported by error page
        // Responds to POST /reportissue
        dd($request);
    }

    public function reportIssue(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'g-recaptcha' => ['required', new GoogleCaptcha],
            'subject' => 'required|min:3',
            'content' => 'required|min:6',
        ], ['g-recaptcha' => ['required' => trans('messages.errors.google_recaptcha_required')]]);
        if (! $validator->passes()) {
            return response()->json(['error' => $validator->errors()]);
        }
        if (Auth::check()) {
            $ticket = new Ticket;

            $ticket->subject = $request->subject;

            $ticket->setPurifiedContent($request->get('content'));

            $ticket->priority_id = 3;
            $ticket->category_id = 1;

            $ticket->status_id = Setting::grab('default_status_id');
            $ticket->user_id = auth()->user()->id;
            $person = Person::find(auth()->user()->id);
            $ticket->orgId = $person->defaultOrgID;

            $ticket->autoSelectAgent();

            $ticket->save();

            return response()->json(['success' => trans('ticketit::lang.the-ticket-has-been-created')]);
        } else {
            return response()->json(['error' => ['member' => trans('messages.errors.ticketit_login_error_404')]]);
        }
        // session()->flash('status', trans('ticketit::lang.the-ticket-has-been-created'));

        // dd($request);
    }
}
