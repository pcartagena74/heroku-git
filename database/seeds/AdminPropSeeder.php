<?php
/**
 * Comment: Seeder for Admin Props tables
 * Created: 3/13/2021
 */

use Illuminate\Database\Seeder;

class AdminPropSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = 'INSERT INTO `admin_group` (id, name, order, viewname)
                VALUES
                    (1,	"messages.admin.api.api_props",1,"v1.auth_pages.admin.panels.event_api");';

        DB::beginTransaction();
        DB::insert($sql);
        DB::commit();

        $sql = 'INSERT INTO `admin_prop`
                VALUES (id, groupID, order, name, displayName, type)
	                (1,1,1,"separator","messages.admin.api.api_sep","string"),
	                (2,1,2,"header","messages.admin.api.api_header","string"),
	                (3,1,3,"btntxt","messages.admin.api.api_btntxt","string"),
	                (4,1,4,"btn_color","messages.admin.api.api_btn_color","string"),
	                (5,1,5,"hdr_color","messages.admin.api.api_hdr_color","string"),
	                (6,1,6,"btn_size","messages.admin.api.api_btn_size","string"),
                    (7,1,7,"var_array","n/a","string"),
                    (8,1,8,"chars","messages.admin.api.api_chars","number"),
	                (9,1,9,"ban_bkgd","messages.admin.api.api_bkgd","string"),
	                (10,1,10,"ban_text","messages.admin.api.api_btxt","string");
	                ';

        DB::beginTransaction();
        DB::insert($sql);
        DB::commit();

        $sql = 'INSERT INTO `org-admin_prop`
                VALUES (orgID, propID, value)
	                (1,1,"|"),
	                (1,2,null),
	                (1,3,"R E G I S T E R"),
	                (1,4,"primary"),
	                (1,5,"purple"),
	                (1,6,"sm"),
	                (1,7,null),
	                (1,8,"300"),
	                (1,9,"primary"),
	                (1,10,"light");
	            ';

        DB::beginTransaction();
        DB::insert($sql);
        DB::commit();
    }
}