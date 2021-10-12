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
                'has_reports' => 1
            ],
            [
                'orgID' => 1,
                'title' => 'evp',
                'has_reports' => 1
            ],
            [
                'orgID' => 1,
                'title' => 'fin',
                'has_reports' => 1
            ],
            [
                'orgID' => 1,
                'title' => 'pd',
                'has_reports' => 1
            ],
            [
                'orgID' => 1,
                'title' => 'biz',
                'has_reports' => 1
            ],
            [
                'orgID' => 1,
                'title' => 'mbr',
                'has_reports' => 1
            ],
            [
                'orgID' => 1,
                'title' => 'mktg',
                'has_reports' => 1
            ],
            [
                'orgID' => 1,
                'title' => 'tech',
                'has_reports' => 1
            ],
        ];

        foreach ($list as $l) {
            DB::beginTransaction();
            $vr = new VolunteerRole;
            $vr->orgID = $l['orgID'];
            $vr->title = $l['title'];
            $vr->has_reports = $l['has_reports'];
            if (isset($p)) {
                $vr->reports_to = $p->id;
            }
            $vr->save();

            if ($l['title'] == 'prez') {
                $p = $vr;
                $p->has_reports = 1;
                $p->reports_to = $p->id;
                $p->save();
            }
            DB::commit();
        }
    }
}
