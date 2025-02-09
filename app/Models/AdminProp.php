<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AdminProp extends Model
{
    // The table
    protected $table = 'admin_prop';

    protected $primaryKey = 'id';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(AdminPropGroup::class, 'groupID', 'id');
        // return $this->hasOne(AdminPropGroup::class, 'id', 'groupID');
    }
}
