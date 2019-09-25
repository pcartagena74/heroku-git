<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSession extends Model
{
    use SoftDeletes;

    protected $table = 'event-sessions';
    protected $primaryKey = 'sessionID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['start', 'end', 'createDate', 'updateDate', 'deleted_at'];


    public function track()
    {
        return $this->belongsTo(Track::class, 'trackID', 'trackID');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID', 'eventID');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticketID', 'ticketID');
    }

    public function speakers()
    {
        return $this->belongsToMany(Speaker::class, 'eventsession_speaker', 'eventsession_id', 'speaker_id');
    }

    public function regsessions()
    {
        return $this->hasMany(RegSession::class, 'sessionID', 'sessionID');
    }

    public function show_speakers()
    {
        $output = "";

        if ($this->speakers !== null) {
            foreach ($this->speakers as $speaker) {
                $speaker->load('person');
                $output .= $speaker->person->showFullName();
                if ($this->speakers->last() != $speaker) {
                    $output .= ", ";
                }
            }
        } else {
            $output = trans('messages.fields.tbd');
        }
        return $output;
    }
}
