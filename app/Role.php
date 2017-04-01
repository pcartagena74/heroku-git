<?php
/**
 * Comment: Entrust Role Class
 * Created: 3/25/2017
 */

namespace App;

use Zizaco\Entrust\EntrustRole;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends EntrustRole
{
    use LogsActivity;
    protected static $logAttributes = ['name', 'display_name', 'description'];
    protected static $ignoreChangedAttributes = ['createDate'];
}
