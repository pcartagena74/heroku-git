<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UrlClick extends Model
{
    // The table
    protected $table = 'sent_emails_url_clicked';

    protected $fillable = [
        'sent_email_id',
        'url',
        'hash',
        'clicks',
    ];

    public function email()
    {
        return $this->belongsTo(EmailSent::class);
    }
}
