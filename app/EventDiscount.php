<?php

namespace App;


class EventDiscount extends Model
{
    // The table
    protected $table = 'event-discounts';
    protected $primaryKey = 'discountID';
    protected $dates = ['createDate', 'updateDate'];

    public function org() {
        return $this->belongsTo(Org::class, 'orgID');
    }

    public function event() {
        return $this->belongsTo(Event::class, 'eventID');
    }
}
