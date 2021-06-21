<?php

namespace App\Imports;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PaymentImport implements ToCollection, WithHeadingRow
{

    protected $bankId = null;

    public function __construct($bankId)
    {
        $this->bankId = $bankId;
    }

    /**
     * @param Collection $payments
     * @return Collection
     */
    public function collection(Collection $collection)
    {

        /*switch ($this->bankId) {

            case '1': //mandiri

                $startDate = Carbon::createFromFormat('d/m/Y H.i.s', $startDate)->subHours(7)->toDateTimeString();
                $endDate = Carbon::createFromFormat('d/m/Y H.i.s', $endDate)->subHours(7)->toDateTimeString();

                break;

            case '2': //bca

                $startDate = Carbon::createFromFormat('d/m/Y', $startDate)->subDay()->setTime(17, 0, 0)->toDateTimeString();
                $endDate = Carbon::createFromFormat('d/m/Y', $endDate)->setTime(16, 59, 59)->toDateTimeString();
                break;

            case '3': //bni

                $startDate = str_replace('.', ':', $startDate);
                $endDate = str_replace('.', ':', $endDate);
                $startDate = Carbon::createFromFormat('d/m/y H:i:s', $startDate)->subHours(7);
                $endDate = Carbon::createFromFormat('d/m/y H:i:s', $endDate)->subHours(7);
                break;

            case '4': // bsm

                $startDate = str_replace('-', '/', $startDate);
                $endDate = str_replace('-', '/', $endDate);
                $startDate = Carbon::createFromFormat('d/m/Y H:i', $startDate)->subHours(7)->subMinute()->toDateTimeString();
                $endDate = Carbon::createFromFormat('d/m/Y H:i', $endDate)->subHours(7)->toDateTimeString();

                break;

            case '5': //bri
                $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->subDay()->setTime(17, 0, 0)->toDateTimeString();
                $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->setTime(16, 59, 59)->toDateTimeString();

                break;

        }*/

//        Log::debug($collection);

        $collection = $collection->map(function ($item, $key) {


            switch ($this->bankId) {

                case '1': //madiri

                    $item["date"] = Carbon::createFromFormat('d/m/Y H.i.s', $item["date"])->subHours(7)->toDateTimeString();
                    $item['amount'] = floatval(str_replace(',', '', $item['amount']));
                    break;

                case '2': //bca

                    $date = Carbon::createFromFormat('d/m/Y', $item["date"])->subDay()->setTime(16, 59, 59);
                    $date = $date->subSeconds($key);
                    $item["date"] = $date->toDateTimeString();

                    $item['amount'] = floatval(str_replace(',', '', $item['amount']));

                    break;

                case '3': //bni
                    $item["date"] = str_replace('.', ':', $item["date"]);
                    $date = Carbon::createFromFormat('d/m/y H:i:s', $item["date"])->subHours(7);
                    $item["date"] = $date->toDateTimeString();

                    $item['amount'] = floatval(str_replace(',', '', $item['amount']));

                    break;

                case '4': //bsm

                    $item["date"] = str_replace('-', '/', $item["date"]);
                    $date = Carbon::createFromFormat('d/m/Y H:i', $item["date"])->subHours(7);
                    $item["date"] = $date->toDateTimeString();

                    $item['amount'] = floatval(str_replace(',', '', $item['amount']));
                    break;

                case '5': //bri

                    $date = Carbon::createFromFormat('m/d/Y', $item["date"])->subDay()->setTime(16, 59, 59);
                    $date = $date->subSeconds($key);
                    $item["date"] = $date->toDateTimeString();

                    $item['amount'] = str_replace('.', '', $item['amount']);
                    $item['amount'] = str_replace(',', '.', $item['amount']);
                    $item['amount'] = floatval(str_replace(',', '.', $item['amount']));

                    break;

            }

            return $item;

        })->filter(function ($item, $key) {
            return $item['amount'] > 0;
        })->sortBy('date');

        $startDate = $collection->first()['date'];
        $endDate = $collection->last()['date'];

        $resumeCsv = [];
        foreach ($collection as $item) {
            if (!isset($resumeCsv[$item['amount']])) {
                $resumeCsv[$item['amount']] = 1;
            } else {
                $resumeCsv[$item['amount']] += 1;
            }
        }

        $payments = Payment::ofMember(env('APP_MEMBER'))
            ->where('payment_at', '>=', $startDate)->where('payment_at', '<=', $endDate)
            ->where('bank_id', $this->bankId)
            ->orderBy('payment_at')->get();

        $resumePayment = [];

        foreach ($payments as $payment) {
            if (!isset($resumePayment[$payment['total_payment']])) {
                $resumePayment[$payment['total_payment']] = 1;
            } else {
                $resumePayment[$payment['total_payment']] += 1;
            }
        }

        foreach ($resumeCsv as $key => $value) {

            if (isset($resumePayment[$key])) {

                if ($value > $resumePayment[$key]) {

                    // insert to payment
                    $queueInsert = $collection->filter(function ($item) use ($key) {
                        return $item['amount'] == $key;
                    });

                    $gap = $value - $resumePayment[$key];

                    foreach ($queueInsert as $queue) {

                        if ($gap > 0) {

                            $payment = Payment::where('member_id', env('APP_MEMBER'))
                                ->where('payment_at', $queue['date'])
                                ->where('bank_id', $this->bankId)
                                ->where('total_payment', $queue['amount'])
                                ->first();

                            if (!$payment) {

                                Payment::create([
                                    'member_id' => env('APP_MEMBER'),
                                    'bank_id' => $this->bankId,
                                    'total_payment' => $queue['amount'],
                                    'payment_at' => $queue['date'],
                                    'description' => 'import adjustment'
                                ]);

                                $gap -= 1;

                            }

                        }


                    }

                }

            } else {

                // inset all with key
                $queueInsert = $collection->filter(function ($item) use ($key) {
                    return $item['amount'] == $key;
                });

                foreach ($queueInsert as $queue) {

                    Payment::create([
                        'member_id' => env('APP_MEMBER'),
                        'bank_id' => $this->bankId,
                        'total_payment' => $queue['amount'],
                        'payment_at' => $queue['date'],
                        'description' => 'import batch'
                    ]);

                }

            }

        }
    }
}
