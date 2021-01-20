<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminProp extends Model
{
    // The table
    protected $table      = 'admin_prop';
    protected $primaryKey = 'id';
    const CREATED_AT      = 'createDate';
    const UPDATED_AT      = 'updateDate';
    protected $dates      = ['createDate', 'updateDate'];

    public function group()
    {
        return $this->belongsTo(AdminPropGroup::class, 'groupID', 'id');
        // return $this->hasOne(AdminPropGroup::class, 'id', 'groupID');
    }
}

