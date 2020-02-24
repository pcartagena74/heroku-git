<?php

namespace App;

use App\Traits\InsertOnDuplicateKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;
    use InsertOnDuplicateKey;

    // The table
    protected $table      = 'person-address';
    protected $primaryKey = 'addrID';
    protected $dates      = ['createDate', 'deleted_at', 'updateDate'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID');
    }
}
