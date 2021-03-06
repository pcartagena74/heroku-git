<?php

namespace App;

use App\EventType;
use GrahamCampbell\Flysystem\Facades\Flysystem;

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

    public function eventTypes()
    {
        // NOT NOT NOT a relationship return but a true function
        return EventType::whereIn('orgID', array(1, $this->orgID))->get();
    }

    public function events()
    {
        return $this->hasMany(Event::class,'orgID', 'orgID');
    }

    public function logo_path()
    {
        $s3m = Flysystem::connection('s3_media');
        $logopath = $s3m->getAdapter()->getClient()->getObjectURL(env('AWS_BUCKET3'), $this->orgPath . "/" . $this->orgLogo);
        return $logopath;
    }

    public function org_URL()
    {
        $u = $this->orgURL;
        if(!preg_match("#^https?://#", $u)){
            $u = "http://" . $u;
        }
        return $u;
    }
}
