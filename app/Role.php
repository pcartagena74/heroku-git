<?php
/**
 * Comment: Entrust Role Class
 * Created: 3/25/2017
 */

namespace App;

use Zizaco\Entrust\EntrustRole;
//use Spatie\Activitylog\Traits\LogsActivity;

class Role extends EntrustRole
{
    //use LogsActivity;
    protected static $logAttributes = ['name', 'display_name', 'description'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function people()
    {
        return $this->belongsToMany(Person::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
