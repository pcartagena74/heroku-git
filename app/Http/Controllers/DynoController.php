<?php

namespace App\Http\Controllers;

use App\Jobs\DynoWakeUpJob;

class DynoController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        dispatch(new DynoWakeUpJob);
    }
}
