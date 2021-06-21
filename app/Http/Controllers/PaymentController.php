<?php

namespace App\Http\Controllers;

use App\Imports\PaymentImport;
use App\Models\Payment;
use App\Models\FaspayNotification;
use App\Models\Donation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    public function index(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'member' => 'required',
                'start_date' => 'required_with:end_date|date_format:Y-m-d H:i:s',
                'end_date' => 'required_with:start_date|date_format:Y-m-d H:i:s',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $payments = Payment::with('bank', 'donation')->ofMember(env('APP_MEMBER'))->latest();

            if ($request->get('total_payment')) {

                $payments = $payments->where('total_payment', 'like', '%' . $request->get('total_payment') . '%');

            }

            if ($request->get('payment_at')) {

                $payments = $payments->whereRaw("(payment_at + interval '7 hour')::date = ? ", [$request->get('payment_at')]);

            }

            if ($request->get('bank_id')) {

                $payments = $payments->where('bank_id', $request->get('bank_id'));

            }

            if ($request->get('start_date') && $request->get('end_date')) {

                $payments = $payments->where('payment_at', '>=', $request->get('start_date'))->where('payment_at', '<=', $request->get('end_date'));

            }

            if ($request->get('status') == 'unclaimed') {

                $payments = $payments->whereNull('claim_at');

            } elseif ($request->get('status') == 'claimed') {

                $payments = $payments->whereNotNull('claim_at');

            }


            $offset = 20;
            if ($request->get('offset')) {

                $offset = $request->get('offset');

            }
            $payments = $payments->paginate($offset);

            $payments = $payments->setCollection(

                $payments->getCollection()
                    ->map(function ($item, $key) {
                        $item->payment_at = $item->payment_at->addHours(7);
                        $item->created_at = $item->created_at->addHours(7);
                        $item->updated_at = $item->updated_at->addHours(7);

                        if ($item->claim_at) $item->claim_at = $item->claim_at->addHours(7);

                        return $item;
                    })
            );

            $res['status'] = 'success';
            $res['message'] = 'Successfully get payments';
            $res['data'] = $payments;

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function store(Request $request)
    {

        $bank = [
            'Mandiri' => 1,
            'BCA' => 2,
            'BNI' => 3,
            'BSM' => 4,
            'BRI' => 5,
            'GoPay' => 6
        ];

        try {

            DB::beginTransaction();

            if (!$request->header('Auth')) {
                throw new Exception("Autenticated");
            }

            $data = "bismillah" . date('Ymd') . count($request->all());

            Log::debug($data);

            $auth = hash('sha512', $data);

            Log::debug($auth);

            if ($auth != $request->header('Auth')) {
                throw new Exception("Autenticated");
            }


            if (!$request->isJson()) {

                throw new Exception('This not accept format.');

            }

            foreach ($request->all() as $payment) {

                $payment['bank_id'] = $bank[$payment['bank_name']];
                $payment['payment_at'] = str_replace('T', ' ', $payment['payment_at']);
                unset($payment['bank_name']);

                Payment::firstOrCreate($payment);

            }

            DB::commit();

            $res['status'] = 'success';
            $res['message'] = 'Successfully create receive funds';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    function parseDateBri($date)
    {

        return $date;
    }

    public function import(Request $request)
    {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'source_file' => 'required',
                'bank_id' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $sourceFile = $request->file('source_file');

            $location = 'uploads';

            $fileName = time() . "-" . rand() . "." .$sourceFile->getClientOriginalExtension();
            $sourceFile->move($location, $fileName);
            $filePath  = public_path('uploads/'.$fileName);

            Log::error('before import');
            Excel::import(new PaymentImport($request->get('bank_id')), $filePath);
            Log::error('after import');

            File::delete($filePath);

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully import payment';
            $res['data'] = null;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 500);

        }

    }

    public function faspayNotification(Request $request)
    {
        $signature = $request->signature;
        $payment_status_code = $request->payment_status_code;
        $bill_no = $request->bill_no;
        $bill_total = $request->bill_total;
        $payment_date = $request->payment_date;
        $payment_channel = $request->payment_channel;

        FaspayNotification::create([
            'code'          => $bill_no,
            'status'        => $payment_status_code,
            'responses'     => json_encode($request->all())
        ]);

        $signature = md5(env('FASPAY_USER_ID') . env('FASPAY_PASSWORD') . $bill_no . $payment_status_code);
        $signature = hash('sha1', $signature);

        try {
            $donation = Donation::where('donation_number', $bill_no)->firstOrFail();

            if (($payment_status_code == '2') && ($signature == $request->signature)) {
                Donation::paidDonation($donation, $bill_total, $donation->bank_id, $payment_date, $payment_channel);
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return [
            "response" => $request->input('request'),
            "trx_id" => $request->trx_id,
            "merchant_id" => $request->merchant_id,
            "merchant" => $request->merchant,
            "bill_no" => $request->bill_no,
            "response_code" => '00',
            "response_desc" => $request->payment_status_desc,
            "response_date" => date('Y-m-d H:i:s')
        ];
    }

    public function faspayRedirect(Request $request)
    {
        $bill_no = $request->bill_no;   
        $member = env('APP_MEMBER');
        $donation_detail_url = env('DONATION_DETAIL_URL', 'id/check/');
        $frontend_url = env('FRONTEND_URL', "https://{$member}/");

        return redirect("{$frontend_url}{$donation_detail_url}{$bill_no}");
    }

}
