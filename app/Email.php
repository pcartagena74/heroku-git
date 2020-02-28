<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\InsertOnDuplicateKey;

class Email extends Model
{
    use SoftDeletes;
    use InsertOnDuplicateKey;
    // The table
    protected $table      = 'person-email';
    protected $primaryKey = 'emailID';
    protected $dates      = ['createDate', 'deleted_at', 'updateDate'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID');
    }
}
