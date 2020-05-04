<?php

namespace App;

use App\Models\EmailCampaignTemplateBlock;
use App\Models\EmailQueue;
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
    protected $dates      = ['createDate', 'deleted_at', 'updateDate', 'sendDate'];

    protected $with = ['template_blocks'];
    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }

    public function emails()
    {
        return $this->hasMany(EmailSent::class, 'campaignID', 'campaignID');
    }

    public function urls()
    {
        return $this->hasManyThrough(UrlClick::class, EmailSent::class, 'campaignID', 'sent_email_id', 'campaignID', 'id');
    }

    public function email_count()
    {
        return $this->hasOne(EmailSent::class, 'campaignID', 'campaignID')
            ->selectRaw('campaignID, count(*) as count')
            ->groupBy('campaignID');
    }

    public function mailgun()
    {
        return $this->hasOne(EmailQueue::class, 'campaign_id', 'campaignID')
            ->selectRaw('campaign_id, sum(sent) as sent, sum(failed) as failed, sum(click) as click, sum(delivered) as delivered, sum(open) as open,count(campaign_id) as total_sent')
            ->groupBy('campaign_id');
    }
    public function template_blocks()
    {
        return $this->hasMany(EmailCampaignTemplateBlock::class, 'campaign_id', 'campaignID')
            ->leftJoin('email_blocks as eb', function ($query) {
                $query->on('email_campaign_template_blocks.block_id', '=', 'eb.id');
            })->select(['email_campaign_template_blocks.*', 'eb.property']);

    }
}
