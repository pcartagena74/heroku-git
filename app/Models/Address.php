<?php

namespace App\Models;

use App\Traits\InsertOnDuplicateKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;
    use InsertOnDuplicateKey;

    // The table
    protected $table = 'person-address';
    protected $primaryKey = 'addrID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'deleted_at', 'updateDate'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID');
    }
}
