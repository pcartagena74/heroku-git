<?php

namespace App;

class OrgDiscount extends Model
{
    // The table
    protected $table = 'org-discounts';
    protected $primaryKey = 'discountID';
    protected $dates = ['createDate', 'updateDate'];

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID');
    }
}
