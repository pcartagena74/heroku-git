<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferLink extends Model
{
    protected $table = 'referer_stats';
    protected $primaryKey = 'refID';
    const CREATED_AT = 'createDate';
    protected $dates = ['createDate'];
}
