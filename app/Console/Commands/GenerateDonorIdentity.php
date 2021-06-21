<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDonorIdentity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:donor-identity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return void
     */
    public function handle()
    {
        $donors = User::query()->whereNull('donor_identity')->whereNotNull('registered_at')->orderBy('registered_at')->orderBy('id')->get();

        foreach ($donors as $key => $donor) {

            print_r("execute {$donors->count()}/{$key}\r\n");

            $donorIdentity = (DB::select("select get_donor_identity('{$donor->registered_at->year}')")[0])->get_donor_identity;

            print_r("donor registered at {$donor->registered_at} donor identity is {$donorIdentity}\r\n");

            $donor->update([
                'donor_identity' => $donorIdentity
            ]);
        }

    }
}
