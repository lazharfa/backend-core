<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\DonorType;
use Excel;

class OldDonorsSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:old:donors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Donatur Lama';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo 'mulai ' . date('Y-m-d H:i:s') . "\n";
        if (env('APP_MEMBER') == 'insanbumimandiri.org') {
            Excel::import(new DonorType, public_path('assets/old_donatur.xlsx'));
        }
        
    }
}
