<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Utils\Curl;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MutasiBankController extends Controller
{
    public function callback(Request $request)
    {
        try {
            $data = $request->all();

            $api_token = 'mehteunol' . env('MUTASI_BANK_API_KEY');

            $token = $data['api_key'];

            if (!strpos($api_token, $token)) {
                throw new Exception("invalid api token");
            }

            $bank = [
                'mandiri' => 1,
                'mandiri_giro' => 1,
                'bca' => 2,
                'bni' => 3,
                'bni_giro' => 3,
                'bsm' => 4,
                'bri' => 5,
                'bsm_cms'   => 4,
                'muamalat'  => 45
            ];

            foreach ($data['data_mutasi'] as $dtm) {

                if ($dtm['type'] == 'CR') {

                    $headers = [
                        "Authorization: $token",
                        'Content-Type: application/json'
                    ];

                    $id = $dtm['id'];

                    list($result_v, $error) = Curl::get("https://mutasibank.co.id/api/v1/validate/$id", $headers);

                    $data_r = json_decode($result_v);

                    if ($data_r->valid) {

                        $paymentAt = Carbon::parse($dtm['transaction_date'])->subHours(7);

                        if ($data['module'] == 'bni_giro') {
                            $paymentAt = Carbon::parse($dtm['transaction_date'])->subHours(7)->format('Y-m-d H:i') . ":00";
                        } elseif ($data['module'] == 'bri') {
                            $paymentAt = Carbon::parse($dtm['transaction_date'])->subHours(7)->subSecond()->addDay();
                        }

                        Payment::firstOrCreate(
                            [
                                'member_id' => env('APP_MEMBER'),
                                'total_payment' => $dtm['amount'],
                                'bank_id' => $bank[$data['module']],
                                'payment_at' => $paymentAt
                            ],
                            [
                                'description' => $dtm['description']
                            ]
                        );
                    }

                }

            }

            return response([
                'status' => 'success',
                'message' => 'Successfully create mutasi.'
            ]);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 400);

        }
    }
}
