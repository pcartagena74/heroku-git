<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgDiscount extends Model
{
    // The table
    protected $table = 'org-discounts';
    protected $primaryKey = 'discountID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID');
    }
}
