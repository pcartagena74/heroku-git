<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission = [
            [
                'name' => 'role-management',
                'display_name' => 'Role Management',
                'description' => 'Display, Create, Edit, Delete Roles',
            ],
            [
                'name' => 'settings-management',
                'display_name' => 'Org Settings Management',
                'description' => 'Manage Org Settings',
            ],
            [
                'name' => 'member-management',
                'display_name' => 'Member Management',
                'description' => 'Display/Edit Member Data',
            ],
            [
                'name' => 'mailing-management',
                'display_name' => 'Mail Management',
                'description' => 'Display, Create, Edit, Delete Mailings',
            ],
            [
                'name' => 'survey-management',
                'display_name' => 'Survey Management',
                'description' => 'Display, Create, Edit, Delete Surveys',
            ],
            [
                'name' => 'event-management',
                'display_name' => 'Event Management',
                'description' => 'Display, Create, Edit, Delete Events',
            ],
            [
                'name' => 'speaker-management',
                'display_name' => 'Speaker Management',
                'description' => 'Speaker Management',
            ],
        ];

        foreach ($permission as $key => $value) {
            Permission::create($value);
        }
    }
}
