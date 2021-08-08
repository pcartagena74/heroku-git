<?php
/**
 * Comment: Entrust Permission Class
 * Created: 3/25/2017
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Zizaco\Entrust\EntrustPermission;

//use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends EntrustPermission
{
    //use LogsActivity;
    protected static $logAttributes = ['name', 'display_name', 'description'];
    protected static $ignoreChangedAttributes = ['created_at'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
