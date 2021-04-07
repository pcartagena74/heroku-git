<?php

namespace App;

use App\Traits\InsertOnDuplicateKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use SoftDeletes;
    use InsertOnDuplicateKey;
    // The table
    protected $table = 'person-email';
    protected $primaryKey = 'emailID';
    protected $dates = ['createDate', 'deleted_at', 'updateDate'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID');
    }
}
