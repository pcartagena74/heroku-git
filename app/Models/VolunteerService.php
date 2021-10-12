<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;

class VolunteerService extends Model
{
    use HasFactory;

    protected $table = 'volunteer_service';
    protected $primaryKey = 'id';
    protected static $logAttributes = ['roleStartDate', 'roleEndDate'];
    protected static $ignoreChangedAttributes = ['created_at', 'updated_at'];

    public function volunteer_role()
    {
        return $this->belongsTo(VolunteerRole::class, 'volunteer_role_id', 'id');
    }

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }
}
