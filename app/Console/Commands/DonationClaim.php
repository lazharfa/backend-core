<?php

namespace App\Console\Commands;

use App\Jobs\SendDonationReceived;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\QurbanOrder;
use Illuminate\Console\Command;

class DonationClaim extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donation:claim';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Claim donation if payment is received';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function sendQurbanNotification($donation)
    {
        $qurban_api_url = env('QURBAN_API_URL');
        if ($qurban_api_url) {
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request('POST', "{$qurban_api_url}/api/guest/qurban-order/claim", [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'code'          => $donation->donation_number,
                        'signature'     => hash('sha512', "{$donation->id}{$donation->donation_number}{$donation->total_donation}")
                    ]
                ]);

                // echo json_encode($token) . "\n";

                // echo $res->getBody() . "\n";

                // dd(json_decode($res->getBody()));
            } catch (\Exception $e) {
                // echo $e->getMessage();
            }
        }
            
    }

    public function handle()
    {
        $donations = Donation::where('expired_at', '>', date("Y-m-d H:i:s"))->whereNull('total_donation')->whereNull('staff_id')->get();

        foreach ($donations as $donation) {

            $totalDonation = $donation->donation + $donation->unique_value;

            $payment = Payment::where('total_payment', $totalDonation)
                ->where('bank_id', $donation->bank_id)
                ->where('member_id', $donation->member_id)
                ->whereNull('claim_at');

            if ($donation->bank_id != 4) {

                $payment = $payment->where('payment_at', '>=', $donation->date_donation);

            } else {

                $payment = $payment->where('payment_at', '>=', now()->subDay(3)->setTime(17, 0, 0, 0));

            }

            $payment = $payment->first();

            $dateNow = date("Y-m-d H:i:s");

            if ($payment) {
                $payment->update([
                    'donation_id' => $donation->id,
                    'claim_at' => $dateNow
                ]);

                $donation->update([
                    'auto_verified_at' => $dateNow,
                    'total_donation' => $totalDonation
                ]);

                if ($donation->type != 'Kurban Web 2021') {
                    SendDonationReceived::dispatchNow($donation);
                }else{
                    $this->sendQurbanNotification($donation);
                    // // Update status NTT
                    // QurbanOrder::where('donation_id', $donation->id)->where('qurban_location_id', 1)
                    //     ->update([
                    //     'qurban_status' => 'Belum Dipotong'
                    // ]);

                    // // Update status non NTT
                    // QurbanOrder::where('donation_id', $donation->id)->where('qurban_location_id', '!=', 1)
                    //     ->update([
                    //     'qurban_status' => 'Siap Dipotong'
                    // ]);
                }
                

                    
            }

        }

    }
}
