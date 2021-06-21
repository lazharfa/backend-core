<?php

namespace App\Console\Commands;

use App\Exports\DonationsExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class WeeklyDonationExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:weekly-donation';

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
     * @return mixed
     */
    public function handle()
    {

        $startDate = now()->subDays(7)->setTime(3, 0,0,0);
        $endDate = now()->setTime(3, 0,0,0);

        $donations = DB::table('latest_donation')
            ->where('date_donation', '>', $startDate)
            ->where('date_donation', '<', $endDate)
            ->get();

        $keys = collect($donations->first())->keys()->all();

        $textSubject = 'Donation from ' . $startDate . ' to ' . $endDate;
        $filePath = 'public/' . time() . '-' . str_random(50) . '.xlsx';

        Excel::store(new DonationsExport($donations, $keys), $filePath);

        Mail::raw($textSubject, function($message) use ($filePath, $textSubject) {

            $message->subject($textSubject);
            $message->from('no-reply@insanbumimandiri.org', 'No Reply');

            $message->to('yulianaepianingsih@insanbumimandiri.org')->cc('singhamlagatari@insanbumimandiri.org');

            $message->attach(storage_path('app/' . $filePath));

        });

    }
}

