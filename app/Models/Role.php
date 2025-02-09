<?php
/**
 * Comment: Entrust Role Class
 * Created: 3/25/2017
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Entrust\EntrustRoleOver as EntrustRole;

//use Spatie\Activitylog\Traits\LogsActivity;

class Role extends EntrustRole
{
    //use LogsActivity;
    protected $table = 'roles';

    protected static $logAttributes = ['name', 'display_name', 'description'];

    protected static $ignoreChangedAttributes = ['createDate'];

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'person_role', 'role_id', 'user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'person_role', 'role_id', 'user_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
