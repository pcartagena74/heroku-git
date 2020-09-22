<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaignLink extends Model
{
    protected $table = 'email_campaign_links';

    public function email_queue()
    {
        return $this->hasManyThrough(EmailQueue::class, EmailQueueLink::class, 'email_campaign_links_id', 'id', 'id', 'email_queue_id');
    }
}
