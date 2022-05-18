<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //ced can view/edit all
        DB::table('grants')->insert([
            [
                'role_name' => 'COLLABORATORE',
                'department_name' => 'SVILUPPO SOFTWARE',
                'grant' => 'VIEW_ALL'
            ],
            [
                'role_name' => 'DIPENDENTE',
                'department_name' => 'SVILUPPO SOFTWARE',
                'grant' => 'VIEW_ALL'
            ],
            [
                'role_name' => 'COLLABORATORE',
                'department_name' => 'SVILUPPO APPLICAZIONI DIGITALI',
                'grant' => 'VIEW_ALL'
            ],
            [
                'role_name' => 'DIPENDENTE',
                'department_name' => 'SVILUPPO APPLICAZIONI DIGITALI',
                'grant' => 'VIEW_ALL'
            ],
        ]);
    }
}
