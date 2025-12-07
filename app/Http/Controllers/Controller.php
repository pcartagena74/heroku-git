<?php

namespace App\Http\Controllers;

use App\Models\Org;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public array $data = [];
    /*
    protected int $counter = 1;
    protected User $user;
    protected Person $currentPerson;
    protected Org $currentOrg;
    protected array $role_list;
    protected array $perm_list;
    */

    /**
     * Set a dynamic property on the base controller.
     *
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Get a dynamic property from the base controller.
     *
     * @param mixed $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * Determine if a dynamic property is set on the base controller.
     *
     * @param mixed $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            if (auth()) {
                $this->user = Auth::user();
                $this->currentPerson = Person::find($this->user->id);
                $this->currentOrg = $this->currentPerson->defaultOrg;
                $this->role_list = $this->user->roles->pluck('name')->toArray();
                $this->perm_list = $this->user->permissions->pluck('name')->toArray();
            }

            return $next($request);
        });
    }
}
