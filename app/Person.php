<?php

namespace App;

//use Spatie\Activitylog\Traits\LogsActivity;

use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    //use LogsActivity;
    use SoftDeletes;

    // The table
    protected $table = 'person';
    protected $primaryKey = 'personID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'deleted_at', 'updateDate', 'lastLoginDate'];


    //protected static $logAttributes = ['login', 'defaultOrgID', 'title', 'compName', 'indName', 'allergenInfo', 'affiliation'];
    //protected static $ignoreChangedAttributes = ['createDate'];

    protected $hidden = [ 'remember_token' ];

    public function roles () {
        return $this->belongsToMany(Role::class, 'person_role', 'user_id', 'role_id');
    }

    public function orgs () {
        return $this->belongsToMany(Org::class, 'org-person', 'personID', 'orgID');
    }

    public function emails () {
        return $this->hasMany(Email::class, 'personID', 'personID');
    }

    public function addresses () {
        return $this->hasMany(Address::class, 'personID', 'personID');
    }

    public function socialites () {
        return $this->hasMany(PersonSocialite::class, 'personID', 'personID');
    }

    public function orgperson () {
        return $this->belongsTo(OrgPerson::class, 'personID', 'personID');
    }

    public function registrations () {
        return $this->hasMany(Registration::class, 'personID', 'personID');
    }

    public function regfinances () {
        return $this->hasMany(RegFinance::class, 'personID', 'personID');
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
