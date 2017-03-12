<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $currentPerson = null;

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
