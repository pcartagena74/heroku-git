<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerRole extends Model
{
    use HasFactory;

    protected $table = 'volunteer_roles';
    protected $primaryKey = 'id';
    protected static $logAttributes = ['title_override', 'reports_to', 'has_reports'];
    protected static $ignoreChangedAttributes = ['created_at'];
    protected $fillable = [
        'id', 'reports_to'
    ];

    public function org ()
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }

    public function parent()
    {
        return $this->belongsTo(VolunteerRole::class, 'reports_to', 'id');
    }

    public function children()
    {
        return $this->hasMany(VolunteerRole::class, 'pid', 'id');
    }

    public function service_role()
    {
        //return $this->hasOne()
    }
}