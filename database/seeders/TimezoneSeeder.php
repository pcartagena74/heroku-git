<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sql = "INSERT INTO `timezone`
                VALUES 
                    ('Alaska GMT -9','-0900','America/Anchorage'),
                    ('Azores GMT -1','-0100','Atlantic/Azores'),
                    ('Belgrade GMT +1','+0100','Europe/Belgrade'),
                    ('Brunei GMT +8','+0800','Asia/Brunei'),
                    ('Caracas GMT -4.30','-0430','America/Caracas'),
                    ('Chicago GMT -6','-0600','America/Chicago'),
                    ('Darwin GMT +9.30','+0930','Australia/Darwin'),
                    ('Denver GMT -7','-0700','America/Denver'),
                    ('Dhaka GMT +6','+0600','Asia/Dhaka'),
                    ('Fiji GMT +12','+1200','Pacific/Fiji'),
                    ('GMT  - 0','-0','Atlantic/Reykjavik'),
                    ('Halifax GMT -4','-0400','America/Halifax'),
                    ('Honolulu GMT -10','-1000','Pacific/Honolulu'),
                    ('Katmandu GMT +5.45','+545','Asia/Kathmandu'),
                    ('Kolkata GMT +5.30','+0530','Asia/Kolkata'),
                    ('Krasnoyarsk GMT +7','+0700','Asia/Krasnoyarsk'),
                    ('Kuwait GMT +3','+0300','Asia/Riyadh'),
                    ('Kwajalein GMT -12','-1200','Pacific/Kwajalein'),
                    ('Los Angeles GMT -8','-0800','America/Los_Angeles'),
                    ('Magadan GMT +11','+1100','Asia/Magadan'),
                    ('Minsk GMT +2','+0200','Europe/Minsk'),
                    ('Muscat GMT +4','+0400','Asia/Dubai'),
                    ('New York GMT -5','-0500','America/New_York'),
                    ('Pacific GMT -11','-1100','Pacific/Pago_Pago'),
                    ('Rangoon GMT +6.30','+0630','Asia/Yangon'),
                    ('Sao Paulo GMT -3','-0300','America/Sao_Paulo'),
                    ('Seoul GMT +9','+0900','Asia/Seoul'),
                    ('South Georgia GMT -2','-0200','Atlantic/South Georgia'),
                    ('St Johns GMT -3.30','-0330','America/St_Johns'),
                    ('Sydney GMT +10','+1000','Australia/Sydney'),
                    ('Tehran GMT +3.30','+0330','Asia/Tehran'),
                    ('Tongatapu GMT +13','+1300','Pacific/Tongatapu'),
                    ('Yekaterinburg GMT +5','+0500','Asia/Yekaterinburg')
                    ";

        DB::beginTransaction();
        DB::insert($sql);
        DB::commit();
    }
}
