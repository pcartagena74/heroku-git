<?php

use Illuminate\Database\Seeder;
use App\Role;

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
                'name' => 'Board',
                'display_name' => 'Board Member',
                'description' => 'Board Member'
            ],
            [
                'name' => 'Volunteer',
                'display_name' => 'Volunteer',
                'description' => 'Volunteer'
            ],
            [
                'name' => 'Priv-Volunteer',
                'display_name' => 'Privileged Volunteer',
                'description' => 'Privileged Volunteer'
            ],
            [
                'name' => 'Speaker',
                'display_name' => 'Speaker',
                'description' => 'Speaker'
            ]
        ];

        foreach ($role as $key => $value) {
            Role::create($value);
        }
    }
}
