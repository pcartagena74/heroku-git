<?php

namespace App\Http\Controllers;

use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LibraryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        return view('v1.auth_pages.library.index');

    }

    public function getExisitingFile()
    {
        $currentPerson = Person::find(auth()->user()->id);
        $currentOrg    = $currentPerson->defaultOrg;
        $list          = getAllDirectoryPathFM($currentOrg);
        $files         = Storage::disk(getDefaultDiskFM())->files($list['campaign']);
        return response()->json(['success' => true, 'files' => $files]);
    }
}
