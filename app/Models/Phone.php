<?php

namespace App\Models;

use App\Models\Person;
use App\Traits\InsertOnDuplicateKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phone extends Model
{
    use InsertOnDuplicateKey;
    use SoftDeletes;

    protected $table = 'person-phone';
    protected $primaryKey = 'phoneID';
    const UPDATED_AT = 'updateDate';
    const CREATED_AT = 'createDate';
    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }
}
