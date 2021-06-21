<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\RecoverMidtransTransaction as ImportTransaction;
use Excel;

class RecoverMidtransTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'midtrans:recover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Midtrans Transaction Recover';

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
        $app_member = explode('.', env('APP_MEMBER'));
        $app_member = $app_member[0];

        Excel::import(new ImportTransaction, public_path('transaction/' . $app_member . '.xlsx'));
    }
}
