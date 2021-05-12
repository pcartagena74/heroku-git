<?php
/**
 * Comment: Given a sessionID, provide the stats (registrations or check-ins and any survey results)
 * @param: $session
 * Created: 10/2/2019
 */

use App\Models\RegSession;

//dd($session->sessionID);
if(null !== $session && null === $es){
    $es = $session->sessionID;
}

$surveys = \App\RSSurvey::where([
    ['sessionID', '=', $es]
])
    ->selectRaw("avg(engageResponse) 'engage', avg(takeResponse) 'take', avg(contentResponse) 'content', avg(styleResponse) 'style', count(*) 'count'")
    ->first();

$rs = RegSession::where([
    ['eventID', '=', $event->eventID],
    ['sessionID', '=', $es]
])->count();

if($rs <= 0){
    $regs = \App\Registration::where('eventID', '=', $event->eventID)->count();
}
?>

@if($rs > 0)
    {!! $rs . " " .  trans_choice('messages.headers.att', $rs) !!}
@elseif($regs > 0)
    {!! $regs . " " .  trans_choice('messages.headers.regs', $regs) !!}
@endif
<br />
@if($surveys->count > 0)
    <b>{!! $surveys->count !!} @lang('messages.surveys.surveys')</b>
    <ul>
        <li> @lang('messages.surveys.engage'): {!! $surveys->engage !!} </li>
        <li> @lang('messages.surveys.take'): {!! $surveys->take !!} </li>
        <li> @lang('messages.surveys.content'): {!! $surveys->content !!} </li>
        <li> @lang('messages.surveys.style'): {!! $surveys->style !!} </li>
    </ul>
    <p>
@endif