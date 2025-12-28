<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

class Org extends Model
{
    // The table
    protected $table = 'organization';

    protected $primaryKey = 'orgID';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    public function orgpeople(): HasManyThrough
    {
        return $this->hasManyThrough(Person::class, OrgPerson::class, 'orgID', 'personID', 'orgID', 'personID');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(OrgDiscount::class, 'orgID', 'orgID');
    }

    public function orgperson(): BelongsTo
    {
        return $this->belongsTo(OrgPerson::class, 'orgID', 'orgID');
    }

    public function defaultPerson(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'org-person', 'orgID', 'personID');
    }

    public function eventTypes()
    {
        // NOT NOT NOT a relationship return but a true function
        return EventType::whereIn('orgID', [1, $this->orgID])->get();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'orgID', 'orgID');
    }

    public function admin_props(): HasMany
    {
        return $this->hasMany(OrgAdminProp::class, 'orgID', 'orgID');
    }

    public function logo_path()
    {
        $logo_filename = $this->orgPath . '/' . $this->orgLogo;
        $s3name = select_bucket('m', config('APP_ENV'));

        try {
            if (Storage::disk($s3name)->exists($logo_filename)) {
                $logopath = Storage::disk($s3name)->url($logo_filename);
            }
        } catch (Exception $e) {
            $logopath = '#';
        }

        return $logopath;
    }

    public function org_URL()
    {
        $u = $this->orgURL;
        if (!preg_match('#^https?://#', $u)) {
            $u = 'http://' . $u;
        }

        return $u;
    }

    public function volunteer_roles(): HasMany
    {
        return $this->hasMany(VolunteerRole::class, 'orgID', 'orgID');
    }
}
