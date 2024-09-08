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

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID');
    }
}
