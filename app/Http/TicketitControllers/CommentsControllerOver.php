<?php

namespace App\Http\TicketitControllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Kordy\Ticketit\Controllers\CommentsController as CommentsController;
use Kordy\Ticketit\Models;

class CommentsControllerOver extends CommentsController
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\Ticketit\IsAdminMiddlewareOver::class, ['only' => ['edit', 'update', 'destroy']]);
        $this->middleware(\App\Http\Middleware\Ticketit\ResAccessMiddlewareOver::class, ['only' => 'store']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'ticket_id' => 'required|exists:ticketit,id',
            'content' => 'required|min:6',
        ]);

        $comment = new Models\Comment;

        $comment->setPurifiedContent($request->get('content'));

        $comment->ticket_id = $request->get('ticket_id');
        $comment->user_id = \Auth::user()->id;
        $comment->save();

        $ticket = Models\Ticket::find($comment->ticket_id);
        $ticket->updated_at = $comment->created_at;
        //added by mufaddal for user / agent badge count
        if ($ticket->user_id != auth()->user()->id) {
            $ticket->user_read = 0;
        }
        if ($ticket->agent_id != auth()->user()->id) {
            $ticket->agent_read = 0;
        }
        $ticket->save();

        return back()->with('status', trans('ticketit::lang.comment-has-been-added-ok'));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): Response
    {
        //
    }
}
