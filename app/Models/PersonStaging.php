<?php

namespace App\Models;

//use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\InsertOnDuplicateKey;
use Illuminate\Database\Eloquent\Model;

class PersonStaging extends Model
{
    //use LogsActivity;
    use InsertOnDuplicateKey;

    // The table
    protected $table = 'person-staging';

    protected $primaryKey = 'personID';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'lastLoginDate' => 'datetime',
    ];

    //protected static $logAttributes = ['prefName', 'login', 'defaultOrgID', 'title', 'compName', 'indName', 'allergenInfo', 'affiliation'];
    //protected static $ignoreChangedAttributes = ['createDate'];

    protected $hidden = ['remember_token'];

    public function emails()
    {
        return $this->hasMany(Email::class, 'emailID');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'addrID');
    }

    public function orgperson()
    {
        return $this->belongsTo(OrgPerson::class, 'personID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function defaultOrg()
    {
        return $this->hasOne(Org::class, 'orgID', 'defaultOrgID');
    }
}
