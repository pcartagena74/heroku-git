<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Event;
use App\Tweet;

class TwitterController extends Controller
{
    public function __construct () {
        //$this->middleware('auth');
    }

    public function index () {
    }

    public function show ($id) {
        // responds to GET /blah/id
        $event = Event::find($id);
        if (Auth::check()) {
            $tweets = Tweet::orderBy('created_at','desc')->paginate(5);
        } else {
            $tweets = Tweet::where('approved',1)->orderBy('created_at','desc')->take(5)->get();
        }
        return view('v1.public_pages.display_tweets', compact('event', 'tweets'));
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
    }

    public function store (Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit ($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update (Request $request, $id) {
        // responds to PATCH /blah/id
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}
