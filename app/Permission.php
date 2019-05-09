<?php
/**
 * Comment: Entrust Permission Class
 * Created: 3/25/2017
 */

namespace App;

use Zizaco\Entrust\EntrustPermission;
//use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends EntrustPermission
{
    //use LogsActivity;

    protected static $logAttributes = ['name', 'display_name', 'description'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
