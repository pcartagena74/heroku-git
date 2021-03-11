<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'event-category';
    protected $primaryKey = 'catID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'catID', 'catID');
    }
}
