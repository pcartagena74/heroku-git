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
                'title_override' => 1
            ],
            [
                'orgID' => 1,
                'title' => 'evp',
                'title_override' => 1
            ],
            [
                'orgID' => 1,
                'title' => 'fin',
                'title_override' => 0
            ],
            [
                'orgID' => 1,
                'title' => 'pd',
                'title_override' => 0
            ],
            [
                'orgID' => 1,
                'title' => 'biz',
                'title_override' => 0
            ],
            [
                'orgID' => 1,
                'title' => 'mbr',
                'title_override' => 0
            ],
            [
                'orgID' => 1,
                'title' => 'mktg',
                'title_override' => 0
            ],
            [
                'orgID' => 1,
                'title' => 'tech',
                'title_override' => 0
            ],
        ];

        foreach ($list as $l) {
            DB::beginTransaction();
            $vr = new VolunteerRole;
            $vr->orgID = $l['orgID'];
            $vr->title = $l['title'];
            $vr->title_override = $l['title_override'];
            if (isset($p)) {
                $vr->pid = $p->id;
            }
            $vr->save();

            if ($l['title'] == 'prez') {
                $p = $vr;
                $p->title_override = 1;
                $p->pid = null;
                $p->save();
            }
            DB::commit();
        }
    }
}
