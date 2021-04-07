<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPropGroup extends Model
{
    // The table
    protected $table = 'admin_group';
    protected $primaryKey = 'id';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];

    public function props()
    {
        return $this->hasMany(AdminProp::class, 'groupID');
    }
}
