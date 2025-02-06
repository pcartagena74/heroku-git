<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OPHistory extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'revision';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'RelDate1' => 'datetime',
        'RelDate2' => 'datetime',
        'RelDate3' => 'datetime',
        'RelDate4' => 'datetime',
        'RelDate5' => 'datetime',
        'RelDate6' => 'datetime',
        'RelDate7' => 'datetime',
        'RelDate8' => 'datetime',
        'RelDate9' => 'datetime',
        'RelDate10' => 'datetime',
        'changeDate' => 'datetime',
    ];

    // The table
    protected $table = 'org-person_history';

    public function myperson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'orgID');
    }

    public function myorg(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'orgID');
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class, 'personID');
    }

    public function orgs(): HasMany
    {
        return $this->hasMany(Org::class, 'orgID');
    }
}
