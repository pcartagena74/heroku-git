<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaignTemplateBlock extends Model
{
    protected $table = 'email_campaign_template_blocks';

    protected $fillable = ['campaign_id', 'block_id', 'content'];
}
