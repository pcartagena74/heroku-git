@php
/**
 * Created: 10/2/2019
 * Comment: Given a sessionID, provide the stats (registrations or check-ins and any survey results)
 * @var $event: passed Event object
 * @var $session: session object
 * @var $es: optionally passed EventSession id
 */

use App\Models\RegSession;
use App\Models\RSSurvey;
use App\Models\Registration;

//dd($session->sessionID);
if(null !== $session && null === $es){
    $es = $session->sessionID;
}

$surveys = RSSurvey::where([
    ['sessionID', '=', $es]
])
    ->selectRaw("avg(engageResponse) 'engage', avg(takeResponse) 'take', avg(contentResponse) 'content', avg(styleResponse) 'style', count(*) 'count'")
    ->first();

$rs = RegSession::where([
    ['eventID', '=', $event->eventID],
    ['sessionID', '=', $es]
])->count();

if($rs <= 0){
    $regs = Registration::where('eventID', '=', $event->eventID)->count();
}
@endphp

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