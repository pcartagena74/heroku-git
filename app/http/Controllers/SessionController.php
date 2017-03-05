<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;

class SessionController extends \App\Http\Controllers\Controller {
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

        public function create () {
        return view('v1.public_pages.home-login');
    }

    public function store (Request $request) {
        if (! auth()->attempt(request(['login', 'password']))) {
            return back();
        }
        return redirect('/dashboard');
    }

    public function logout (Request $request) {
        auth()->logout();
        $request->session()->flush();
        return redirect('/');
    }
}
