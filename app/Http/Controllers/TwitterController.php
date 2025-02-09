<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwitterController extends Controller
{
    public function __construct()
    {
        //$this->middleware('guest');
    }

    public function index() {}

    public function show(Event $event): View
    {
        // responds to GET /blah/id
        // $event = Event::find($id);
        if (Auth::check()) {
            $tweets = Tweet::orderBy('created_at', 'desc')->paginate(5);
        } else {
            $tweets = Tweet::where('approved', 1)->orderBy('created_at', 'desc')->take(5)->get();
        }

        return view('v1.public_pages.display_tweets', compact('event', 'tweets'));
    }

    public function create()
    {
        // responds to /blah/create and shows add/edit form
    }

    public function store(Request $request)
    {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id)
    {
        // responds to PATCH /blah/id
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
    }
}
