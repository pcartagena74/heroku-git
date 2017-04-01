<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;

class Person extends Model
{
    use LogsActivity;

    // The table
    protected $table = 'person';
    protected $primaryKey = 'personID';
    protected $dates = ['createDate', 'deleted_at', 'updateDate', 'lastLoginDate'];

    protected static $logAttributes = ['prefName', 'login', 'defaultOrgID', 'title',
        'compName', 'indName', 'allergenInfo', 'affiliation'];
    protected static $ignoreChangedAttributes = ['createDate'];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function emails () {
        return $this->hasMany(Email::class, 'emailID');
    }

    public function addresses () {
        return $this->hasMany(Address::class, 'addrID');
    }

    public function orgperson () {
        return $this->belongsTo(OrgPerson::class, 'personID');
    }

    public function user () {
        return $this->belongsTo(User::class, 'id');
    }

    public function defaultOrg () {
        return $this->hasOne(Org::class, 'orgID', 'defaultOrgID');
    }
}
