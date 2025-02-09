<?php

namespace App\Models;

//use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\InsertOnDuplicateKey;
use Illuminate\Database\Eloquent\Model;

class PersonStaging extends Model
{
    //use LogsActivity;
    use InsertOnDuplicateKey;

    // The table
    protected $table = 'person-staging';

    protected $primaryKey = 'personID';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'lastLoginDate' => 'datetime',
    ];

    //protected static $logAttributes = ['prefName', 'login', 'defaultOrgID', 'title', 'compName', 'indName', 'allergenInfo', 'affiliation'];
    //protected static $ignoreChangedAttributes = ['createDate'];

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
