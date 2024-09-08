<?php

namespace App\Models\Entrust;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
//use Zizaco\Entrust\Contracts\EntrustRoleInterface;
use Shanmuga\LaravelEntrust\Contracts\LaravelEntrustRoleInterface as EntrustRoleInterface;
//use Zizaco\Entrust\Traits\EntrustRoleTrait;
use Shanmuga\LaravelEntrust\Traits\LaravelEntrustRoleTrait as EntrustRoleTrait;

class EntrustRoleOver extends Model implements EntrustRoleInterface
{
    use EntrustRoleTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('entrust.roles_table');
    }

    public function toggle($id, $touch = null)
    {
        dd('here');
    }
}
