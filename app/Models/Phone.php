<?php

namespace App\Models;

use App\Traits\InsertOnDuplicateKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phone extends Model
{
    use InsertOnDuplicateKey;
    use SoftDeletes;

    protected $table = 'person-phone';

    protected $primaryKey = 'phoneID';

    const UPDATED_AT = 'updateDate';

    const CREATED_AT = 'createDate';

    protected function casts(): array
    {
        return [
            'createDate' => 'datetime',
            'updateDate' => 'datetime',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }
}
