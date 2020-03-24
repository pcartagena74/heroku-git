<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = [
            [
                'name'         => 'Board',
                'display_name' => 'Board Member',
                'description'  => 'Volumneer who may need administrative access to any area of this site.',
            ],
            [
                'name'         => 'Speaker',
                'display_name' => 'Speaker',
                'description'  => 'Speaker: This role does NOT provide any administrative access.',
            ],
            [
                'name'         => 'Event-Volunteer',
                'display_name' => 'Events Committee',
                'description'  => 'Volunteer who may need administrative access to manage events.',
            ],
            [
                'name'         => 'Volunteer',
                'display_name' => 'Volunteer',
                'description'  => 'Volunteer:  This role does not provide any administrative access to this site.',
            ],
            [
                'name'         => 'PMI MassBay',
                'display_name' => 'PMI MassBay',
                'description'  => 'This role must be associated with anyone who needs access to site permissions and other roles.',
            ],
            [
                'name'         => 'Speaker-Volunteer',
                'display_name' => 'Speaker Committee Volunteer',
                'description'  => 'Volunteer who may need to administrate Speaker-related settings in this site',
            ],
            [
                'name'         => 'Roundtable-Volunteer',
                'display_name' => 'Roundtable Leader',
                'description'  => 'Volunteer who may need administrative access to manage events.',
            ],
            [
                'name'         => 'Admin',
                'display_name' => 'Admin',
                'description'  => 'Features meant only for admins',
            ], [
                'name'         => 'Developer',
                'display_name' => 'Developer',
                'description'  => 'Features that are under development',
            ], [
                'name'         => 'Marketing',
                'display_name' => 'Marketing Volunteer',
                'description'  => 'Volunteers requiring Marketing Access',
            ],
        ];

        foreach ($role as $key => $value) {
            $each = Role::create($value);
        }
        
    }
}
