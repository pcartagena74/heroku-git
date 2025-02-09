<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

//use Spatie\Activitylog\Traits\LogsActivity;

class EventDiscount extends Model
{
    use SoftDeletes;
    //use LogsActivity;

    // The table
    protected $table = 'event-discounts';

    protected $primaryKey = 'discountID';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    protected static $logAttributes = ['percent', 'flatAmt', 'discountCODE'];

    protected static $ignoreChangedAttributes = ['createDate'];

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'orgID');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'eventID');
    }
}
