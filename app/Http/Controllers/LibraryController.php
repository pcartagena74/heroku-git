<?php

namespace App\Http\Controllers;

use App\Models\Person;
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
        $currentOrg = $currentPerson->defaultOrg;
        $list = getAllDirectoryPathFM($currentOrg);
        // $files         = Storage::disk(getDefaultDiskFM())->files($list['campaign']);
        $files = array_filter(Storage::disk(getDefaultDiskFM())->files($list['campaign']), function ($file) {
            return preg_match('/^.*\.(jpg|jpeg|png|gif)$/i', $file);
        });
        $file_list_url = [];
        foreach ($files as $key => $value) {
            // $file_list_url[] = url($value);
            $file_list_url[] = Storage::disk(getDefaultDiskFM())->url($value);
        }

        return response()->json(['success' => true, 'files' => $file_list_url]);
    }

    public function getFile(Request $request, $org_path, $folder_name, $file_name)
    {
        if (empty($org_path) || empty($folder_name) || empty($file_name)) {
            abort(404);
        }
        $path = $org_path.'\\'.$folder_name.'\\'.$file_name;
        if (Storage::disk(getDefaultDiskFM())->exists($path)) {
            $content = Storage::disk(getDefaultDiskFM())->get($path);
            // $headers = ["Content-Type" => mime_content_type($path)];
            header('Content-Type: '.mime_content_type(Storage::disk(getDefaultDiskFM())->path($path)));
            echo file_get_contents($content);
        // return response()->file($path, $headers);
        } else {
            abort(404);
        }
    }

    public function getCompleteURL(Request $request)
    {
        $url = $request->input('url');
        if (! empty(trim($url))) {
            return Storage::disk(getDefaultDiskFM())->url($url);
        }

        return response()->json(['success' => false, 'url' => $url]);
    }
}
