<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;
use App\EventDiscount;

class RoleController extends Controller
{
    public function __construct () {
        //$this->middleware('auth', ['except' => ['showDiscount']]);
    }

    public function index() {
        // responds to /blah
    }

    public function show($id) {
        // responds to GET /blah/id
    }

    public function create() {
        // responds to /blah/create and shows add/edit form
    }

    public function store(Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id) {
        // responds to PATCH /blah/id
    }

    public function destroy($id) {
        // responds to DELETE /blah/id
    }
}
