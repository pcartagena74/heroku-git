<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // echo bcrypt('mufaddal');die();
        return view('v1.public_pages.home-login');
    }

    public function store(Request $request)
    {
        // This is the function that processes issues reported by error page
        // Responds to POST /reportissue
    }
}
