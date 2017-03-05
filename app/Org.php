<?php

namespace App;


class Org extends Model
{
    // The table
    protected $table = 'organization';
    protected $primaryKey = 'orgID';
    protected $dates = ['createDate', 'updateDate'];

    public function orgpeople() {
        return $this->hasMany(OrgPerson::class, 'personID', 'orgID');
    }

    public function discounts() {
        return $this->hasMany(OrgDiscount::class, 'discountID', 'orgID');
    }

    public function orgperson() {
        return $this->belongsTo(OrgPerson::class, 'orgID', 'orgID');
    }

    public function defaultOrg() {
        return $this->belongsTo(OrgPerson::class, 'orgID', 'orgID');
    }
}
