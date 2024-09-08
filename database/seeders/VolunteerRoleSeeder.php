<?php

namespace Database\Seeders;

use App\Models\VolunteerRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VolunteerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list = [
            [
                'orgID' => 1,
                'title' => 'prez',
                'prefix_override' => 1,
            ],
            [
                'orgID' => 1,
                'title' => 'evp',
                'prefix_override' => 1,
            ],
            [
                'orgID' => 1,
                'title' => 'fin',
                'prefix_override' => 0,
            ],
            [
                'orgID' => 1,
                'title' => 'pd',
                'prefix_override' => 0,
            ],
            [
                'orgID' => 1,
                'title' => 'biz',
                'prefix_override' => 0,
            ],
            [
                'orgID' => 1,
                'title' => 'mbr',
                'prefix_override' => 0,
            ],
            [
                'orgID' => 1,
                'title' => 'mktg',
                'prefix_override' => 0,
            ],
            [
                'orgID' => 1,
                'title' => 'tech',
                'prefix_override' => 0,
            ],
        ];

        foreach ($list as $l) {
            DB::beginTransaction();
            $vr = new VolunteerRole;
            $vr->orgID = $l['orgID'];
            $vr->title = $l['title'];
            $vr->prefix_override = $l['prefix_override'];
            if (isset($p)) {
                $vr->pid = $p->id;
            }
            $vr->save();

            if ($l['title'] == 'prez') {
                $p = $vr;
                $p->prefix_override = 1;
                $p->pid = null;
                $p->save();
            }
            DB::commit();
        }
    }
}
