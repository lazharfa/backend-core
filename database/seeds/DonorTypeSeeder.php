<?php

use Illuminate\Database\Seeder;
use App\Imports\DonorType;

class DonorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Excel::import(new DonorType, public_path('assets/old_donatur.xlsx'));
    }
}
