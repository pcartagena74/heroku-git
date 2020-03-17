<?php

namespace App\Http\TicketitControllers;

use Illuminate\Http\Request;
use Kordy\Ticketit\Controllers\CommentsController as CommentsController;
use Kordy\Ticketit\Models;

class CommentsControllerOver extends CommentsController
{
    public function __construct()
    {
        $this->middleware('\App\Http\Middleware\Ticketit\IsAdminMiddlewareOver', ['only' => ['edit', 'update', 'destroy']]);
        $this->middleware('Kordy\Ticketit\Middleware\ResAccessMiddleware', ['only' => 'store']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'ticket_id' => 'required|exists:ticketit,id',
            'content'   => 'required|min:6',
        ]);

        $comment = new Models\Comment();

        $comment->setPurifiedContent($request->get('content'));

        $comment->ticket_id = $request->get('ticket_id');
        $comment->user_id   = \Auth::user()->id;
        $comment->save();

        $ticket             = Models\Ticket::find($comment->ticket_id);
        $ticket->updated_at = $comment->created_at;
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
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
