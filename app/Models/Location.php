<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    // The table
    protected $table = 'event-location';
    protected $primaryKey = 'locID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'deleted_at', 'updateDate'];

    public function event()
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }
}