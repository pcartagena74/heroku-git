<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    protected $table = 'person-phone';
    protected $primaryKey = 'phoneID';
    const UPDATED_AT = 'updateDate';
    const CREATED_AT = 'createDate';
    protected $dates = ['createDate', 'updateDate'];

    public function person() {
        return $this->belongsTo(Person::class, 'personID');
    }
}