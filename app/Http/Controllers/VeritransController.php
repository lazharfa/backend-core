<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Payment;
use App\Utils\Message;
use App\Veritrans\Veritrans;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VeritransController extends Controller
{
    public function __construct()
    {
        Veritrans::$serverKey = env('SERVER_KEY_VERITRANS', '');

        //set Veritrans::$isProduction  value to true for production mode
        Veritrans::$isProduction = env('PRODUCTION_VERITRANS', false);
    }

    public function notification(Request $request)
    {
        DB::beginTransaction();
        $veritrans = new Veritrans;
        $payloads = $veritrans->status($request->get('order_id'));
        $donation = Donation::where('donation_number', ($request->get('order_id')))->firstOrFail();

        $campaignTitle = $donation->campaign ? $donation->campaign->campaign_title : $donation->type_donation;
        $message = null;
        $paymentMethod = false;

        if ($payloads->transaction_status == 'settlement') {

            if ($payloads->fraud_status == 'accept') {

                $timeNow = now();

                Payment::updateOrCreate(
                    [
                        'member_id' => env('APP_MEMBER'),
                        'total_payment' => $payloads->gross_amount,
                        'bank_id' => env('GOPAY_ID', 6),
                        'payment_at' => Carbon::parse($payloads->transaction_time)->subHour(7)
                    ],
                    [
                        'description' => 'pay with ' . $payloads->payment_type,
                        'donation_id' => $donation->id,
                        'claim_at' => $timeNow
                    ]
                );

                $donation = Donation::where('donation_number', ($request->get('order_id')))->firstOrFail();

                $donation->update([
                    'unique_value' => 0,
                    'auto_verified_at' => $timeNow,
                    'total_donation' => $payloads->gross_amount
                ]);

            }

        } elseif ($payloads->transaction_status == 'pending') {

            if (isset($payloads->va_numbers)) { // bank bni

                $vaNumber = $payloads->va_numbers[0]->va_number;

                $bank = $payloads->va_numbers[0]->bank;

                $paymentMethod = "{$bank} virtual account {$vaNumber}";

            } elseif (isset($payloads->bill_key)) { // bank mandiri

                $vaNumber = $payloads->bill_key;
                $billerCode = $payloads->biller_code;

                $paymentMethod = "mandiri kode {$billerCode}, virtual account {$vaNumber}";

            } elseif (isset($payloads->permata_va_number)) { // bank permata
                $vaNumber = $payloads->permata_va_number;

                $paymentMethod = "permata kode virtual account {$vaNumber}";

            } elseif (isset($payloads->payment_code)) { // alfamart

                // expired + 14 day
                $vaNumber = $payloads->payment_code;

                $paymentMethod = "almart kode pembayaran {$vaNumber}";

            }

            $message = "Terimakasih {$donation->donor_name}. Mohon transfer {$donation->donation} ke {$paymentMethod} untuk program {$campaignTitle}";

            if (env('GUIDE_MIDTRANS', false) && $donation->donor_phone && $paymentMethod) {
                Message::zenzivaV1($message, $donation->donor_phone);
            }

        }

        DB::commit();

    }
}
