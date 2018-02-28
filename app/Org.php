<?php

namespace App;

class Org extends Model
{
    // The table
    protected $table = 'organization';
    protected $primaryKey = 'orgID';
    protected $dates = ['createDate', 'updateDate'];

    public function orgpeople()
    {
        return $this->hasManyThrough(Person::class, OrgPerson::class, 'orgID', 'personID', 'orgID', 'personID');
    }

    public function discounts()
    {
        return $this->hasMany(OrgDiscount::class, 'orgID', 'orgID');
    }

    public function orgperson()
    {
        return $this->belongsTo(OrgPerson::class, 'orgID', 'orgID');
    }

    public function defaultPerson()
    {
        return $this->belongsToMany(Person::class, 'org-person', 'orgID', 'personID');
    }
}
