<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailList extends Model
{
    use SoftDeletes;

    // The table
    protected $table = 'email-list';

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }
}
