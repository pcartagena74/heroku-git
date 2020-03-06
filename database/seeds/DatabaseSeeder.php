<?php

use App\Org;
use App\Permission;
use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            PermissionTableSeeder::class,
            RoleTableSeeder::class,
        ]);

        $role       = Role::findOrFail(9);
        $permission = Permission::get();
        foreach ($permission as $key => $value) {
            $role->attachPermission($value);
        }
        $org  = Org::first();
        $user = User::first();
        // DB::insert();
        // $user->attachRole($role, ['orgID', $org->id]);
    }
}
