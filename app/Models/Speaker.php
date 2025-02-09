<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Speaker extends Model
{
    protected $table = 'speaker';

    protected $primaryKey = 'id';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected function casts(): array
    {
        return [
            'createDate' => 'datetime',
            'updateDate' => 'datetime',
        ];
    }

    public function eventsessions(): BelongsToMany
    {
        return $this->belongsToMany(EventSession::class, 'eventsession_speaker', 'speaker_id', 'eventsession_id');
    }

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'personID', 'id');
    }
}
