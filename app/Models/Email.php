<?php

namespace App\Models;

use App\Traits\InsertOnDuplicateKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use SoftDeletes;
    use InsertOnDuplicateKey;
    // The table
    protected $table = 'person-email';
    protected $primaryKey = 'emailID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'deleted_at', 'updateDate'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID');
    }
}
