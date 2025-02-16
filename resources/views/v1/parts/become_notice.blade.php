@php
/**
 * Comment: Display to keep a "you've become XYZ" banner persistent until ended
 * Created: 2/5/2019
 */

//Session::put('become', $new_id);
//Session::put('prior_id', auth()->user()->id);

use App\Models\Person;

// Read in session variables
$become = Session::get('become');
$prior_id = Session::get('prior_id');

$p = Person::find($become);

// Re-write the session variables so they persist
Session::put('become', $become);
Session::put('prior_id', $prior_id);
Session::save();
@endphp
{{ html()->form('POST', env('APP_URL') . "/become")->open() }}
{{ html()->hidden('new_id', $prior_id) }}
{{ html()->hidden('cancel', 1) }}
<div class="form-group" style="margin-top:55px;">
    <p class="alert alert-warning">
        <a aria-label="close" class="close" data-dismiss="alert" href="#">
            ×
        </a>
        {!!  trans('messages.messages.become', ['name' => $p->showFullName()]) !!}    
        {{ html()->submit(trans('messages.buttons.unbecome'))->class('btn btn-primary btn-xs') }}
    </p>
</div>
{{ html()->form()->close() }}
