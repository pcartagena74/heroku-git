<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;

class EventDiscount extends Model
{
    use LogsActivity;

    // The table
    protected $table = 'event-discounts';
    protected $primaryKey = 'discountID';
    protected $dates = ['createDate', 'updateDate'];

    protected static $logAttributes = ['percent', 'flatAmt', 'discountCODE'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function org() {
        return $this->belongsTo(Org::class, 'orgID');
    }

    public function event() {
        return $this->belongsTo(Event::class, 'eventID');
    }
}
