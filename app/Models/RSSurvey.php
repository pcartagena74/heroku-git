<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RSSurvey extends Model
{
    //use LogsActivity;
    // The table
    protected $table = 'rs-survey';

    protected $primaryKey = 'id';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected function casts(): array
    {
        return [
            'createDate' => 'datetime',
            'updateDate' => 'datetime',
        ];
    }

    //protected static $logAttributes = ['confirmation', 'pmtRecd', 'status', 'cost'];
    //protected static $ignoreChangedAttributes = ['createDate'];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'sessionID', 'sessionID');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'regID', 'regID');
    }
}
