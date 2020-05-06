<?php

use App\Org;
use App\Permission;
use App\Role;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            EmailBuilderSeeder::class,
            TicketitSeeder::class,
            RoleSeeder::class,
            CertificationSeeder::class,
            EventTypeSeeder::class,
            IndustrySeeder::class,
            LocationSeeder::class,
            CategorySeeder::class,
            PrefixSeeder::class,
            TimezoneSeeder::class,
            UserRoleSeeder::class,
        ]);

        $roles      = Role::find([9, 8]);
        $permission = Permission::get();
        foreach ($roles as $key => $r_value) {
            foreach ($permission as $p_key => $p_value) {
                $r_value->attachPermission($p_value);
            }
        }
        $org       = Org::first();
        $user      = User::first();
        $all_roles = Role::all();
        $bulk      = [];
        foreach ($all_roles as $key => $value) {
            $bulk[] = ['role_id' => $value->id, 'user_id' => $user->id, 'orgID' => $org->orgID];
        }
        DB::table('role_user')->insert($bulk);
    }
}
