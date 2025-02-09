<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    // The table
    protected $table = 'event-location';

    protected $primaryKey = 'locID';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $fillable = ['locName', 'orgID'];

    protected function casts(): array
    {
        return [
            'createDate' => 'datetime',
            'updateDate' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }
}
