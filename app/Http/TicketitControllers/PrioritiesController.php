<?php

namespace App\Http\TicketitControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models\Priority;

class PrioritiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // seconds expected for L5.8<=, minutes before that
        $time = LaravelVersion::min('5.8') ? 60 * 60 : 60;
        $priorities = \Cache::remember('ticketit::priorities', $time, function () {
            return Priority::all();
        });

        return view('ticketit::admin.priority.index', compact('priorities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('ticketit::admin.priority.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'color' => 'required',
        ]);

        $priority = new Priority;
        $priority->create(['name' => $request->name, 'color' => $request->color]);

        Session::flash('status', trans('ticketit::lang.priority-name-has-been-created', ['name' => $request->name]));

        \Cache::forget('ticketit::priorities');

        return redirect()->action([self::class, 'index']);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        return trans('ticketit::lang.priority-all-tickets-here');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $priority = Priority::findOrFail($id);

        return view('ticketit::admin.priority.edit', compact('priority'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'color' => 'required',
        ]);

        $priority = Priority::findOrFail($id);
        $priority->update(['name' => $request->name, 'color' => $request->color]);

        Session::flash('status', trans('ticketit::lang.priority-name-has-been-modified', ['name' => $request->name]));

        \Cache::forget('ticketit::priorities');

        return redirect()->action([self::class, 'index']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $priority = Priority::findOrFail($id);
        $name = $priority->name;
        $priority->delete();

        Session::flash('status', trans('ticketit::lang.priority-name-has-been-deleted', ['name' => $name]));

        \Cache::forget('ticketit::priorities');

        return redirect()->action([self::class, 'index']);
    }
}
