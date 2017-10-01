<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    // The table
    protected $table = 'org-campaign';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';

    protected $primaryKey = 'campaignID';
    protected $dates = ['createDate', 'deleted_at', 'updateDate', 'sendDate'];

    public function org() {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }

    public function emails(){
        return $this->hasMany(EmailSent::class, 'campaignID', 'campaignID');
    }

    public function urls(){
        return $this->hasManyThrough(UrlClick::class, EmailSent::class, 'campaignID', 'sent_email_id', 'id', 'id');
    }

    public function email_count(){
        return $this->hasOne(EmailSent::class, 'campaignID', 'campaignID')
                    ->selectRaw('campaignID, count(*) as count')
                    ->groupBy('campaignID');
    }
}
