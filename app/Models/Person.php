<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Person extends Model
{
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    // The table
    protected $table = 'person';

    protected $primaryKey = 'personID';

    protected $fillable = ['prefix', 'firstName', 'lastName', 'prefName', 'login', 'title', 'compName', 'indName'];

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'lastLoginDate' => 'datetime',
    ];

    protected $hidden = ['remember_token'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected static $logAttributes = ['login', 'defaultOrgID', 'defaultOrgPersonID'];

    protected static $ignoreChangedAttributes = ['createDate'];

    public function roles(): BelongsToMany
    {
        // we need to get default person org id so running another query to fetch same
        // $person = Person::find(auth()->user()->id);

        // NOTE: person_role is a view, not a table.
        return $this->belongsToMany(Role::class, 'person_role', 'user_id', 'role_id')
            ->using(PersonRoleOrgPivot::class)->withPivot('org_id');
        //->where('person_role.org_id', $this->defaultOrgID);
    }

    public function orgs(): BelongsToMany
    {
        return $this->belongsToMany(Org::class, 'org-person', 'personID', 'orgID');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class, 'personID', 'personID');
    }

    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class, 'personID', 'personID');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'personID', 'personID');
    }

    public function socialites(): HasMany
    {
        return $this->hasMany(PersonSocialite::class, 'personID', 'personID');
    }

    public function orgperson(): HasOne
    {
        return $this->hasOne(OrgPerson::class, 'id', 'defaultOrgPersonID');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'personID', 'personID');
    }

    public function regfinances(): HasMany
    {
        return $this->hasMany(RegFinance::class, 'personID', 'personID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'personID', 'id');
    }

    public function defaultOrg(): BelongsTo
    {
        // Alternate approach to getting the default organization
        // return $this->orgs()->where('org-person.orgID', $this->defaultOrgID);
        return $this->belongsTo(Org::class, 'defaultOrgID', 'orgID');
    }

    public function orgStat1()
    {
        if ($this->orgperson !== null) {
            return $this->orgperson->OrgStat1;
        } else {
            return null;
        }
    }

    public function showDisplayName()
    {
        if ($this->prefName) {
            return $this->prefName;
        } else {
            return $this->firstName;
        }
    }

    public function showFullName()
    {
        if ($this->prefName) {
            return $this->prefName.' '.$this->lastName;
        } else {
            return $this->firstName.' '.$this->lastName;
        }
    }

    public function routeNotificationForMail()
    {
        return $this->login;
    }

    public function org_role_id()
    {
        return Role::where('name', $this->defaultOrg->orgName)->select('id')->first();
    }

    // returns the OrgStat1 associated with $orgID if populated or null
    public function is_member($orgID)
    {
        $personID = $this->personID;

        return DB::table('person')
            ->join('org-person', function ($join) use ($orgID, $personID) {
                $join->on('person.personID', '=', 'org-person.personID')
                    ->where([
                        ['org-person.orgID', '=', $orgID],
                        ['person.personID', '=', $personID],
                    ])->whereNotNull('OrgStat1');
            })->select('OrgStat1')->first();
    }

    public function speaker()
    {
        $this->hasOne(Speaker::class, 'id', 'personID');
    }

    public function add_speaker_role()
    {
        //$org_role     = $this->org_role_id()->id;
        $speaker_role = 2;
        if (! $this->roles->contains('id', $speaker_role)) {
            $this->roles()->attach($speaker_role, ['org_id' => $this->defaultOrgID]);
        }
        /*
        if (!$this->roles->contains('id', $this->org_role_id()->id)) {
            $this->roles()->attach($org_role);
        }
        */
        $s = Speaker::find($this->personID);
        if ($s === null) {
            $s = new Speaker;
            $s->id = $this->personID;
            $s->save();
        }
    }

    public function email(): HasMany
    {
        return $this->hasMany(Email::class, 'personID', 'personID')->where('isPrimary', 1);
    }

    public function service_role(): HasOne
    {
        return $this->hasOne(VolunteerService::class, 'personID', 'personID');
    }

    public function has_volunteers()
    {
        if ($this->service_role !== null) {
            return $this->service_role->volunteer_role->children;
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
