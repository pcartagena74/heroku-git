<?php

use App\Email;
use App\Org;
use App\OrgPerson;
use App\Person;
use App\User;
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

        $org = new Org;
        $org->orgName = 'Efcico Corporation';
        $org->orgPath = '/efcico';
        $org->eventEmail = 'events@gmail.com';
        $org->orgZone = '-0500';                    // Default timezone of Eastern Standard Time
        $org->save();

        $list = [
            [
                'id' => '0',
                'firstName' => 'No',
                'lastName' => 'Body',
                'email' => 'nobody@mcentric.org',
            ],
            [
                'id' => '1',
                'firstName' => 'System',
                'lastName' => 'Admin',
                'email' => 'admin@mcentric.org',
            ],
        ];

        foreach ($list as $l) {
            DB::beginTransaction();
            $p = new Person;
            $p->personID = $l->id;
            $p->firstName = $l->firstName;
            $p->prefName = $l->firstName;
            $p->lastName = $l->lastName;
            $p->login = $l->email;
            $p->defaultOrgID = 0;
            $p->creatorID = 0;
            $p->updaterID = 0;
            $p->save();

            $op = new OrgPerson;
            $op->OrgStat0 = 1;
            $op->personID = $p->personID;
            $op->orgID = 0;
            $op->creatorID = 0;
            $op->updaterID = 0;
            $op->save();

            $p->defaultOrgPersonID = $op->id;
            $p->save();

            $u = new User;
            $u->id = $p->personID;
            $u->login = $l->email;
            $u->name = $l->email;
            $u->email = $l->email;
            $u->password = bcrypt('password');
            $u->save();

            $e = new Email;
            $e->emailADDR = $l->email;
            $e->personID = $p->personID;
            $e->isPrimary = 0;
            $e->creatorID = 0;
            $e->updaterID = 0;
            $e->save();

            DB::commit();
        }
    }
}
