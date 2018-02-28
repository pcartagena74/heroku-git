<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailList extends Model
{
    use SoftDeletes;

    // The table
    protected $table = 'email-list';
    protected $dates = ['created_at', 'deleted_at', 'updated_at'];

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }
}
