<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'sendDate' => 'datetime',
    ];

    protected $with = ['template_blocks'];

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(EmailSent::class, 'campaignID', 'campaignID');
    }

    public function urls(): HasManyThrough
    {
        return $this->hasManyThrough(UrlClick::class, EmailSent::class, 'campaignID', 'sent_email_id', 'campaignID', 'id');
    }

    public function email_count(): HasOne
    {
        return $this->hasOne(EmailSent::class, 'campaignID', 'campaignID')
            ->selectRaw('campaignID, count(*) as count')
            ->groupBy('campaignID');
    }

    public function mailgun(): HasOne
    {
        return $this->hasOne(EmailQueue::class, 'campaign_id', 'campaignID')
            ->selectRaw('campaign_id, sum(sent) as sent, sum(permanent_fail) as permanent_fail, sum(click) as click, sum(delivered) as delivered, sum(open) as open,count(campaign_id) as total_sent,sum(temporary_failure) as temporary_failure,sum(unsubscribe) as unsubscribe,sum(spam) as spam, SUM(CASE WHEN device_type = "mobile" THEN 1 else 0 END ) AS mobile_count, SUM( CASE WHEN device_type = "desktop" THEN 1 else 0 END ) AS desktop_count')
            ->groupBy('campaign_id');
    }

    public function template_blocks(): HasMany
    {
        return $this->hasMany(EmailCampaignTemplateBlock::class, 'campaign_id', 'campaignID')
            ->leftJoin('email_blocks as eb', function ($query) {
                $query->on('email_campaign_template_blocks.block_id', '=', 'eb.id');
            })->select(['email_campaign_template_blocks.*', 'eb.property']);
    }

    public function campaign_links(): HasMany
    {
        return $this->hasMany(EmailCampaignLink::class, 'campaign_id', 'campaignID');
    }

    public function email_queue(): HasManyThrough
    {
        return $this->hasManyThrough(UrlClick::class, EmailSent::class, 'campaignID', 'sent_email_id', 'campaignID', 'id');
    }
}
