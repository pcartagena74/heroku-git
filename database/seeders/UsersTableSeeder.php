<?php

namespace Database\Seeders;

use App\Models\Email;
use App\Models\Org;
use App\Models\OrgPerson;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $user = DB::table('users')->insert([
        //     'name'     => Str::random(10),
        //     'email'    => 'system_admin@gmail.com',
        //     'password' => bcrypt('password'),
        //     'login'    => 'system_admin@gmail.com',
        // ]);

        DB::statement('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');

        $org = new Org;
        $org->orgName = 'Efcico Corporation';
        $org->orgPath = '/efcico';
        $org->eventEmail = 'events@gmail.com';
        $org->orgZone = '-0500';                    // Default timezone of Eastern Standard Time
        $org->OSN1 = trans('messages.headers.profile_vars.orgstat1');
        $org->OSN2 = trans('messages.headers.profile_vars.orgstat2');
        $org->OSN3 = trans('messages.headers.profile_vars.orgstat3');
        $org->OSN4 = trans('messages.headers.profile_vars.orgstat4');
        $org->ODN1 = trans('messages.headers.profile_vars.reldate1');
        $org->ODN2 = trans('messages.headers.profile_vars.reldate2');
        $org->ODN3 = trans('messages.headers.profile_vars.reldate3');
        $org->ODN4 = trans('messages.headers.profile_vars.reldate4');
        $org->save();

        $list = [
            [
                'id' => '1',
                'firstName' => 'System',
                'lastName' => 'Admin',
                'email' => 'admin@mcentric.org',
            ],
            [
                'id' => '0',
                'firstName' => 'No',
                'lastName' => 'Body',
                'email' => 'nobody@mcentric.org',
            ],
        ];

        foreach ($list as $l) {
            DB::beginTransaction();
            $p = new Person;
            $p->personID = $l['id'];
            $p->firstName = $l['firstName'];
            $p->prefName = $l['firstName'];
            $p->lastName = $l['lastName'];
            $p->login = $l['email'];
            $p->defaultOrgID = $org->orgID;
            $p->creatorID = 0;
            $p->updaterID = 0;
            $p->save();

            if($l['id'] == 0){
                $p->personID = 0;
                $p->save();
            }

            $op = new OrgPerson;
            $op->OrgStat1 = null;
            $op->personID = $p->personID;
            $op->orgID = $org->orgID;
            $op->creatorID = 0;
            $op->updaterID = 0;
            $op->save();

            $p->defaultOrgPersonID = $op->id;
            $p->save();

            $u = new User;
            $u->id = $p->personID;
            $u->login = $p->login;
            $u->name = $p->login;
            $u->email = $p->login;
            $u->password = bcrypt('password');
            $u->save();

            $e = new Email;
            $e->emailADDR = $p->login;
            $e->personID = $p->personID;
            $e->isPrimary = 0;
            $e->creatorID = 0;
            $e->updaterID = 0;
            $e->save();

            DB::commit();
        }
    }
}
