<?php

namespace App\Console\Commands;

use App\Exports\DonationsExport;
use App\Models\Donation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class MonthlyReportExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:monthly-donation';

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

        if (env('APP_MEMBER') == 'insanbumimandiri.org') {

            $startDate = now()->startOfMonth()->subMonth()->subHours(7)->toDateTimeString();
            $endDate = now()->startOfMonth()->subHours(7)->toDateTimeString();

            $donations = Donation::select(DB::raw("donor_email email, donor_phone as phone, substring(trim(donor_name) FROM '^([^ ]+)') as fn, substring(trim(donor_name) FROM '([^ ]+)$') ln, created_at"))
                ->where('date_donation', '>', $startDate)
                ->where('date_donation', '<', $endDate)
                ->whereNotNull('total_donation')
                ->get();

            $keys = collect($donations->first())->keys()->all();

            $filePath = 'public/' . time() . '-' . str_random(50) . '.xlsx';
            $textSubject = 'Donation from ' . $startDate . ' to ' . $endDate;

            Excel::store(new DonationsExport($donations, $keys), $filePath);

            Mail::raw($textSubject, function($message) use ($filePath, $textSubject) {

                $message->subject($textSubject);
                $message->from('no-reply@insanbumimandiri.org', 'No Reply');

                $message->to('acq@insanbumimandiri.org');

                $message->attach(storage_path('app/' . $filePath));

            });

        }

    }
}
