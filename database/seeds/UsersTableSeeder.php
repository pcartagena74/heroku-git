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

        $org       = new Org;
        $org->orgName = 'First Organization';
        $org->orgPath = '/firstorg';
        $org->eventEmail = 'events@gmail.com';
        $org->orgZone = '-0500';                    // Default timezone of Eastern Standard Time
        $org->save();


        $firstName = 'System';
        $lastName  = 'Admin';
        $email     = 'system_admin@gmail.com';

        DB::beginTransaction();
        $p               = new Person;
        $p->firstName    = $firstName;
        $p->prefName     = $firstName;
        $p->lastName     = $lastName;
        $p->login        = $email;
        $p->defaultOrgID = 1;
        $p->creatorID    = 1;
        $p->updaterID    = 1;
        $p->save();

        $op            = new OrgPerson;
        $op->OrgStat1  = 1;
        $op->personID  = $p->personID;
        $op->orgID     = 1;
        $op->creatorID = 1;
        $op->updaterID = 1;
        $op->save();
        
        $p->defaultOrgPersonID = $op->id;
        $p->save();

        $u           = new User;
        $u->id       = $p->personID;
        $u->login    = $email;
        $u->name     = $email;
        $u->email    = $email;
        $u->password = bcrypt('password');
        $u->save();

        $e            = new Email;
        $e->emailADDR = $email;
        $e->personID  = $p->personID;
        $e->isPrimary = 1;
        $e->creatorID = 1;
        $e->updaterID = 1;
        $e->save();

        
        DB::commit();
    }
}
