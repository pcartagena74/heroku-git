<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Person;

class SpeakerController extends Controller
{
    public function index () {
        $speaker_list = null;
        $speakers = Person::whereHas('roles', function($q) {
            $q->where('roles.id', '=', '2');
        })
                          ->select('firstName', 'lastName', 'login')
                          ->get();

        return view('v1.auth_pages.speakers.speaker', compact('speakers'));
    }

    public function index2 () {
        return view('v1.auth_pages.speakers.speaker', compact('url'));
    }
}
