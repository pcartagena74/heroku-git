<?php

namespace App\Traits;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
//use Zizaco\Entrust\Traits\EntrustUserTrait;
use Shanmuga\LaravelEntrust\Traits\LaravelEntrustUserTrait as EntrustUserTrait;

trait EntrustUserTraitOver
{
    use EntrustUserTrait;

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param  mixed  $role
     * @param  array  $extra_data  in our case orgID
     */
    public function attachRole($role, ?array $extra_data = null)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->attach($role, $extra_data);
    }

    /**
     * Big block of caching functionality.
     *
     * @return mixed Roles
     */
    public function cachedRoles()
    {
        $userPrimaryKey = $this->primaryKey;
        $cacheKey = 'entrust_roles_for_user_'.$this->$userPrimaryKey;
        if (Cache::getStore() instanceof TaggableStore && false) {
            return Cache::tags(Config::get('entrust.role_user_table'))->remember($cacheKey, Config::get('cache.ttl'), function () {
                return $this->roles()->get();
            });
        } else {
            return $this->roles()->get();
        }
    }
}
