<?php

use Illuminate\Database\Seeder;
use App\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = [
            [
                'name' => 'role-management',
                'display_name' => 'Display, Create, Edit, Delete Roles',
                'description' => 'Perform Role Management'
            ],
            [
                'name' => 'settings-management',
                'display_name' => 'Org Settings Management',
                'description' => 'Perform Org Settings Management'
            ],
            [
                'name' => 'member-management',
                'display_name' => 'Display/Edit Member Listing',
                'description' => 'Perform Member Management'
            ],
            [
                'name' => 'mailing-management',
                'display_name' => 'Display, Create, Edit, Delete Mailings',
                'description' => 'Perform Mailing Management'
            ],
            [
                'name' => 'survey-management',
                'display_name' => 'Display, Create, Edit, Delete Surveys',
                'description' => 'Perform Survey Management'
            ],
            [
                'name' => 'event-management',
                'display_name' => 'Display, Create, Edit, Delete Events',
                'description' => 'Perform Event Management'
            ],
            [
                'name' => 'speaker-management',
                'display_name' => 'Speaker Management',
                'description' => 'Speaker Management'
            ]
        ];

        foreach ($permission as $key => $value) {
            Permission::create($value);
        }
    }
}
