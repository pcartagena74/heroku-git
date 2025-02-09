<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function email(): BelongsTo
    {
        return $this->belongsTo(EmailSent::class);
    }
}
