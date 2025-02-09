<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PersonHistory extends Model
{
    // The table
    protected $table = 'person_history';

    protected $primaryKey = 'revision';

    protected $casts = [
        'changeDate' => 'datetime',
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'lastLoginDate' => 'datetime',
    ];

    protected $hidden = ['remember_token'];

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class, 'emailID');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'addrID');
    }

    public function orgperson(): BelongsTo
    {
        return $this->belongsTo(OrgPerson::class, 'personID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function defaultOrg(): HasOne
    {
        return $this->hasOne(Org::class, 'orgID', 'defaultOrgID');
    }
}
