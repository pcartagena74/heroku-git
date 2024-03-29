<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonHistory extends Model
{
    // The table
    protected $table = 'person_history';
    protected $primaryKey = 'revision';
    protected $casts = [
        'changeDate' => 'datetime',
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'lastLoginDate' => 'datetime',
    ];

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
