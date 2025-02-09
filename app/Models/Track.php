<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Track extends Model
{
    use SoftDeletes;

    protected $table = 'event-tracks';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $primaryKey = 'trackID';

    protected function casts(): array
    {
        return [
            'createDate' => 'datetime',
            'updateDate' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'eventID');
    }

    //public function sessions() {
    //    return $this->hasMany(EventSession::class, 'sessionID');
    //}
}
