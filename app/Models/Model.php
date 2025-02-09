<?php
/**
 * Comment:
 * Created: 2/2/2017
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $guarded = [];

    protected static $logOnlyDirty = true;
}
