<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailQueueLink extends Model
{
    protected $table = 'email_queue_links';

    public function email_queue()
    {
        return $this->hasManyThrough(EmailQueueLink::class, EmailQueue::class, 'email_campaign_link_id', 'id', 'email_queue_id', 'id');
        return $this->hasMany(EmailQueue::class, 'campaign_id', 'campaignID');
    }
}