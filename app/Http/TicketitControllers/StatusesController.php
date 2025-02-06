<?php

namespace App\Http\TicketitControllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models\Status;

class StatusesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // seconds expected for L5.8<=, minutes before that
        $time = LaravelVersion::min('5.8') ? 60 * 60 : 60;
        $statuses = \Cache::remember('ticketit::statuses', $time, function () {
            return Status::all();
        });

        return view('ticketit::admin.status.index', compact('statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('ticketit::admin.status.create');
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

        $status = new Status;
        $status->create(['name' => $request->name, 'color' => $request->color]);

        Session::flash('status', trans('ticketit::lang.status-name-has-been-created', ['name' => $request->name]));

        \Cache::forget('ticketit::statuses');

        return redirect()->action([self::class, 'index']);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        return trans('ticketit::lang.status-all-tickets-here');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $status = Status::findOrFail($id);

        return view('ticketit::admin.status.edit', compact('status'));
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

        $status = Status::findOrFail($id);
        $status->update(['name' => $request->name, 'color' => $request->color]);

        Session::flash('status', trans('ticketit::lang.status-name-has-been-modified', ['name' => $request->name]));

        \Cache::forget('ticketit::statuses');

        return redirect()->action([self::class, 'index']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $status = Status::findOrFail($id);
        $name = $status->name;
        $status->delete();

        Session::flash('status', trans('ticketit::lang.status-name-has-been-deleted', ['name' => $name]));

        \Cache::forget('ticketit::statuses');

        return redirect()->action([self::class, 'index']);
    }
}
