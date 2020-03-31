<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        return view('v1.auth_pages.library.index');

    }
}
