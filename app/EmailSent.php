<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailSent extends Model
{
    // The table
    protected $table = 'sent_emails';

    protected $fillable = [
        'hash',
        'headers',
        'sender',
        'recipient',
        'subject',
        'content',
        'opens',
        'clicks',
        'message_id',
        'meta',
    ];

    public function email()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function urls()
    {
        return $this->hasMany(UrlClick::class, 'sent_email_id');
    }

    public function url_count()
    {
        return $this->hasOne(UrlClick::class, 'sent_email_id', 'id')
                    ->selectRaw('sent_email_id, count(*) as count')
                    ->groupBy('sent_email_id');
    }

    public function click_count()
    {
        return $this->hasOne(UrlClick::class, 'sent_email_id', 'id')
            ->selectRaw('sent_email_id, sum(clicks) as clicks')
            ->groupBy('sent_email_id');
    }
}
