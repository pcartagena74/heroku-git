<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class EmailCampaignLink extends Model
{
    protected $table = 'email_campaign_links';

    public function email_queue(): HasManyThrough
    {
        return $this->hasManyThrough(EmailQueue::class, EmailQueueLink::class, 'email_campaign_links_id', 'id', 'id', 'email_queue_id');
    }
}
