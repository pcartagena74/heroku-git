<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrgAdminProp extends Model
{
    // The table
    protected $table = 'org-admin_prop';
    //protected $primaryKey = ['orgID', 'propID'];
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];
    protected $fillable = ['orgID', 'propID', 'value'];

    protected function setKeysForSaveQuery(Builder $query)
    {
        return $query->where('orgID', $this->getAttribute('orgID'))
            ->where('propID', $this->getAttribute('propID'));
    }

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }

    public function prop()
    {
        return $this->belongsTo(AdminProp::class, 'propID', 'id');
        //return $this->hasOne(AdminProp::class, 'id', 'propID');
    }
}
