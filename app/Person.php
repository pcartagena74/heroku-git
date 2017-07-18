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

    protected static $logAttributes = ['login', 'defaultOrgID', 'title',
        'compName', 'indName', 'allergenInfo', 'affiliation'];
    protected static $ignoreChangedAttributes = ['createDate'];

    protected $hidden = [ 'remember_token' ];

    public function roles () {
        return $this->belongsToMany(Role::class, 'person_role', 'user_id', 'role_id');
    }

    public function emails () {
        return $this->hasMany(Email::class, 'personID', 'personID');
    }

    public function addresses () {
        return $this->hasMany(Address::class, 'personID', 'personID');
    }

    public function orgperson () {
        return $this->belongsTo(OrgPerson::class, 'personID', 'personID');
    }

    public function user () {
        return $this->belongsTo(User::class, 'id', 'personID');
    }

    public function defaultOrg () {
        return $this->hasOne(Org::class, 'orgID', 'defaultOrgID');
    }

    public function showDisplayName(){
        if($this->prefName){
            return $this->prefName;
        } else {
            return $this->firstName;
        }
    }

    public function showFullName(){
        return $this->firstName . " " . $this->lastName;
    }
}
