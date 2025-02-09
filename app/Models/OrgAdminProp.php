<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgAdminProp extends Model
{
    // The table
    protected $table = 'org-admin_prop';

    //protected $primaryKey = ['orgID', 'propID'];
    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    protected $fillable = ['orgID', 'propID', 'value'];

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('orgID', $this->getAttribute('orgID'))
            ->where('propID', $this->getAttribute('propID'));
    }

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }

    public function prop(): BelongsTo
    {
        return $this->belongsTo(AdminProp::class, 'propID', 'id');
        //return $this->hasOne(AdminProp::class, 'id', 'propID');
    }
}
