<?php

use Illuminate\Database\Seeder;
use App\Imports\FaqImport;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Excel::import(new FaqImport, storage_path('app/faq/insanbumimandiri.xlsx'));
    }
}
