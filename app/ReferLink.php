<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferLink extends Model
{
    protected $table = 'referer_stats';
    protected $primaryKey = 'refID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];
}
