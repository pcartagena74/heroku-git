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
{!! Form::open(array('url' => env('APP_URL')."/become", 'method' => 'POST')) !!}
{!! Form::hidden('new_id', $prior_id) !!}
{!! Form::hidden('cancel', 1) !!}
<div class="form-group" style="margin-top:55px;">
    <p class="alert alert-warning">
        <a aria-label="close" class="close" data-dismiss="alert" href="#">
            ×
        </a>
        {!!  trans('messages.messages.become', ['name' => $p->showFullName()]) !!}    
        {!! Form::submit(trans('messages.buttons.unbecome'), array('class' => 'btn btn-primary btn-xs')) !!}
    </p>
</div>
{!! Form::close() !!}
