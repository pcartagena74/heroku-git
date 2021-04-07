<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PersonRoleOrgPivot extends Pivot
{
    protected $table = 'person_role';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'user_id', 'personID');
    }

    public function role()
    {
        $this->belongsTo(Role::class);
    }

    public function org()
    {
        $this->belongsTo(Org::class, 'org_id', 'orgID');
    }
}
