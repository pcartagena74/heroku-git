<?php
/**
 * Comment: Entrust Role Class
 * Created: 3/25/2017
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Entrust\EntrustRoleOver as EntrustRole;
//use Spatie\Activitylog\Traits\LogsActivity;

class Role extends EntrustRole
{
    //use LogsActivity;
    protected $table = 'roles';
    protected static $logAttributes = ['name', 'display_name', 'description'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function people()
    {
        return $this->belongsToMany(Person::class, 'person_role', 'role_id', 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'person_role', 'role_id', 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
