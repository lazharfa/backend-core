<?php

namespace App\Http\Controllers;

use App\Exports\DonationsExport;
use App\Exports\DonationExtraTemplate;
use App\Imports\DonationExtraImport;
use App\Jobs\GuideDonation;
use App\Jobs\SendDonationReceived;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\QurbanOrder;
use App\Models\MemberBank;
use App\Models\User;
use App\Transformers\Date;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Image;
use App\Traits\Transaction;
use App\Console\Commands\ReminderDonation;
use App\Traits\Helper;
use Illuminate\Validation\Rule;

class DonationController extends Controller
{
    use Transaction, Helper;

    public function index(Request $request)
    {
        $donations = Donation::with('campaign:id,category_id,campaign_title,campaign_slug,campaign_status,campaign_image,target_donation,expired_at,invitation_message,created_at', 'bank', 'staff:id,full_name,email')->ofMember(env('APP_MEMBER'))->whereNotIn('bank_id', explode(',', env('HIDE_ON_DASHBOARD', 0)))->latest();

        if (($request->get('min_donation') != null) and ($request->get('max_donation') != null)) {

            $donations = $donations->where(DB::raw("donation + unique_value"), '>=', $request->get('min_donation'))->where(DB::raw("donation + unique_value"), '<=', $request->get('max_donation'));

        }

        if ($request->get('status') == 'verified') {

            $donations = $donations->whereNotNull('total_donation');

        } elseif ($request->get('status') == 'unverified') {

            $donations = $donations->whereNull('total_donation');

        } elseif ($request->get('status') == 'offline') {

            $donations = $donations->whereNotNull('staff_id');

        }

        if ($request->get('start_date') && $request->get('end_date')) {

            $donations = $donations->where('date_donation', '>=', $request->get('start_date'))->where('date_donation', '<=', $request->get('end_date'));

        }

        if ($request->get('email')) {
            $donations = $donations->where('staff_id', function ($query) use ($request) {

                $query
                    ->select('id')
                    ->from('users')
                    ->where('member_id', env('APP_MEMBER'))
                    ->where('email', $request->get('email'))
                    ->first();

            });

        }

        if ($request->get('donor_type')) {

            if ($request->get('donor_type') == 'All') {

                $donations = $donations->whereNotNull('donor_type');

            } else {

                $donations = $donations->where('donor_type', $request->get('donor_type'));

            }

        }

        if ($request->get('type_donation')) {

            $donations = $donations->where('type_donation', $request->get('type_donation'));

        }

        if ($request->get('note')) {

            $donations = $donations->where('note', 'like', '%' . $request->get('note') . '%');

        }

        if ($request->get('expired') == true) {

            $donations = $donations->where('expired_at', '>', now());

        }

        if ($request->get('order') && $request->get('order_type')) {

            $donations = $donations->orderBy($request->get('order'), $request->get('order_type'));

        }

        $offset = 20;
        if ($request->get('offset')) {
            $offset = $request->get('offset');
        }

        $donations = $donations
            ->extraDonationFilter($request->extra)
            ->bankIdFilter($request->get('bank_id'))
            ->officerFilter($request->officer)
            ->statusFilter($request->statusFilter)
            ->search($request->str)
            ->sort($request->sort)
            ->paginate($offset);

        $timezone = 7;
        if ($request->get('timezone')) {
            $timezone = (int)$request->get('timezone');
        }

        $donations = $donations->setCollection(

            $donations->getCollection()->map(function ($donation) use ($timezone) {

                $donation->date_donation = Date::ChangeTimezone($timezone, $donation->date_donation);
                $donation->expired_at = Date::ChangeTimezone($timezone, $donation->expired_at);

                if ($donation->verified_at) $donation->verified_at = Date::ChangeTimezone($timezone, $donation->verified_at);
                if ($donation->auto_verified_at) $donation->auto_verified_at = Date::ChangeTimezone($timezone, $donation->auto_verified_at);

                $donation->created_at = Date::ChangeTimezone($timezone, $donation->created_at);
                $donation->updated_at = Date::ChangeTimezone($timezone, $donation->updated_at);

                return $donation;

            })
        );

        $res['status'] = 'success';
        $res['message'] = 'Successfully get donations';
        $res['data'] = $donations;

        return response($res, 200);

    }

    public function list(Request $request)
    {

        $donations = DB::table('donation_view')->where('member_id', env('APP_MEMBER'));

        if ($request->get('str')) {

            $donations = $donations->where(function ($query) use ($request) {
                $query
                    ->orWhereRaw('lower(donor_name) like ?', [strtolower('%' . $request->get('str') . '%')])
                    ->orWhereRaw('lower(donor_phone) like ?', [strtolower('%' . $request->get('str') . '%')])
                    ->orWhereRaw('lower(donor_email) like ?', [strtolower('%' . $request->get('str') . '%')])
                    ->orWhereRaw('(donation + unique_value)::text like ?', [strtolower('%' . $request->get('str') . '%')]);
            });

        }

        if (($request->get('min_donation') != null) and ($request->get('max_donation') != null)) {

            $donations = $donations->where(DB::raw("donation + unique_value"), '>=', $request->get('min_donation'))->where(DB::raw("donation + unique_value"), '<=', $request->get('max_donation'));

        }

        if ($request->get('status') == 'verified') {

            $donations = $donations->whereNotNull('total_donation');

        } elseif ($request->get('status') == 'unverified') {

            $donations = $donations->whereNull('total_donation');

        } elseif ($request->get('status') == 'offline') {

            $donations = $donations->whereNotNull('staff_id');

        }

        if ($request->get('start_date') && $request->get('end_date')) {

            $donations = $donations->where('date_donation', '>=', $request->get('start_date'))->where('date_donation', '<=', $request->get('end_date'));

        }

        if ($request->get('type_donation')) {

            $donations = $donations->where('type_donation', $request->get('type_donation'));

        }

        if ($request->get('note')) {

            $donations = $donations->where('note', 'like', '%' . $request->get('note') . '%');

        }

        if ($request->get('bank_id')) {

            $donations = $donations->where('bank_id', $request->get('bank_id'));

        }

        if ($request->get('order') && $request->get('order_type')) {

            $donations = $donations->orderBy($request->get('order'), $request->get('order_type'));

        }

        if ($request->get('download')) {

            return Excel::download(new DonationsExport($donations->get()), time() . '-' . str_random(50) . '.xlsx');

        }


        $offset = 20;
        if ($request->get('offset')) {
            $offset = $request->get('offset');
        }
        $donations = $donations->paginate($offset);

        $timezone = 7;
        if ($request->get('timezone')) {

            $timezone = (int)$request->get('timezone');

        }

        $donations = $donations->setCollection(

            $donations->getCollection()->map(function ($donation) use ($timezone) {

                $donation->date_donation = Date::ChangeTimezone($timezone, $donation->date_donation);
                return $donation;

            })

        );

        $res['status'] = 'success';
        $res['message'] = 'Successfully get donations';
        $res['data'] = $donations;

        return response($res, 200);

    }

    public function download(Request $request)
    {
        try {
            $donations = DB::table('donation_view')->where('member_id', env('APP_MEMBER'));

            if ($request->get('str')) {

                $donations = $donations->where(function ($query) use ($request) {
                    $query
                        ->orWhereRaw('lower(donor_name) like ?', [strtolower('%' . $request->get('str') . '%')])
                        ->orWhereRaw('lower(donor_phone) like ?', [strtolower('%' . $request->get('str') . '%')])
                        ->orWhereRaw('lower(donor_email) like ?', [strtolower('%' . $request->get('str') . '%')])
                        ->orWhereRaw('(donation + unique_value)::text like ?', [strtolower('%' . $request->get('str') . '%')]);
                });

            }

            if (($request->get('min_donation') != null) and ($request->get('max_donation') != null)) {

                $donations = $donations->where(DB::raw("donation + unique_value"), '>=', $request->get('min_donation'))->where(DB::raw("donation + unique_value"), '<=', $request->get('max_donation'));

            }

            if ($request->get('status') == 'verified') {

                $donations = $donations->whereNotNull('total_donation');

            } elseif ($request->get('status') == 'unverified') {

                $donations = $donations->whereNull('total_donation');

            } elseif ($request->get('status') == 'offline') {

                $donations = $donations->whereNotNull('staff_id');

            }

            if ($request->get('start_date') && $request->get('end_date')) {

                $donations = $donations->where('date_donation', '>=', $request->get('start_date'))->where('date_donation', '<=', $request->get('end_date'));

            }

            if ($request->get('email')) {

                $donations = $donations->where('staff_id', function ($query) use ($request) {

                    $query
                        ->select('id')
                        ->from('users')
                        ->where('member_id', env('APP_MEMBER'))
                        ->where('email', $request->get('email'))
                        ->first();

                });

            }

            if ($request->get('donor_type')) {

                if ($request->get('donor_type') == 'All') {

                    $donations = $donations->whereNotNull('donor_type');

                } else {

                    $donations = $donations->where('donor_type', $request->get('donor_type'));

                }

            }

            if ($request->get('type_donation')) {

                $donations = $donations->where('type_donation', $request->get('type_donation'));

            }

            if ($request->get('note')) {

                $donations = $donations->where('note', 'like', '%' . $request->get('note') . '%');

            }

            if ($request->get('bank_id')) {

                $donations = $donations->where('bank_id', $request->get('bank_id'));

            }


            $donations = $donations->get();

            $keys = collect($donations->first())->keys()->all();

            $filePath = 'public/' . time() . '-' . str_random(50) . '.xlsx';

            Excel::store(new DonationsExport($donations, $keys), $filePath);

            $emailTarget = Auth::user()->email;
            $textSubject = 'Donation from ' . $request->get('start_date') . ' to ' . $request->get('end_date');

            Mail::raw($textSubject, function ($message) use ($filePath, $emailTarget, $textSubject) {

                $message->subject($textSubject);
                $message->from('no-reply@insanbumimandiri.org', 'No Reply');

                $message->to($emailTarget);

                $message->attach(storage_path('app/' . $filePath));

            });

            $res['status'] = 'success';
            $res['message'] = 'Successfully download donations';
            $res['data'] = '';

            return response($res, 200);
        } catch (Exception $e) {
            $res['status'] = 'error';
            $res['message'] = $e->getMessage();
            $res['data'] = null;

            return response($res, 400);
        }
        

    }

    public function summary()
    {
        return Cache::remember('donation-summary', now()->addMinutes(5), function () {

            $campaign = Campaign::ofMember(env('APP_MEMBER'))->get();

            return collect([
                'total_donor' => DB::table('donors')->count(),
                'total_campaign' => $campaign->count(),
                'total_fund' => $campaign->sum('total_fund')
            ]);

        });

    }

    public function byDonor(Request $request, $donorId)
    {

        try {

            DB::beginTransaction();

            $offset = 15;

            if ($request->get('offset')) {
                $offset = $request->get('offset');
            }

            $donations = Donation::with('campaign', 'bank', 'staff')->ofMember(env('APP_MEMBER'))->where('donor_id', $donorId)->get();
            $res['status'] = 'success';
            $res['message'] = 'Successfully get donations by user';
            $res['data'] = $donations->paginate($offset);
            DB::commit();

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

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'member_id' => 'required',
                'campaign_id' => 'required_without:type_donation|exists:campaigns,id',
                'registered_at' => 'date_format:Y-m-d',
                'date_donation' => 'required',
                'donation' => 'required',
                'type_donation' => 'required_without:campaign_id',
                'bank_id' => 'required|exists:member_banks,id',
                'donor_phone' => 'required_without:donor_email|min:9|regex:/^[0-9]+$/',
                'donor_email' => 'required_without:donor_phone|email|max:255',
                'donor_name' => 'required',
                'anonymous' => 'required',
                'payment_id' => 'required_with:total_donation',
                'total_donation' => 'required_with:payment_id',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            if ($request->get('campaign_id') && !$request->get('staff_id')) {

                $campaign = Campaign::find($request->get('campaign_id'));

                if ($campaign->expired_at) {
                    if (now() > $campaign->expired_at) {
                        throw new Exception('Campaign has ended.');
                    }
                }
            }

            $dateDonation = Carbon::parse($request->get('date_donation'));

            if ($dateDonation > now()) {

                $dateDonation = $dateDonation->subHours(7);

                // karena bank yang dari mutasi bank jamnya d set k 23:00
                if ($request->input('bank_id') != 5) {

                    if ($dateDonation > now()) {
                        throw new Exception("Tanggal masa depan tidak bisa di input");
                    }
                }
            }

            if ($dateDonation < now()->subMonth()) {
                throw new Exception("Tanggal terlalu lama, tidak bisa di input");
            }

            $payment = null;
            $currentTimestamp = date("Y-m-d H:i:s");

            if ($request->get('payment_id') && $request->get('total_donation')) {
                $payment = Payment::findOrFail($request->get('payment_id'));

                if ($request->get('bank_id') != 5) {
                    $dateDonation = $payment->payment_at;
                }

                $request->request->add([
                    'date_donation' => $dateDonation,
                    'verified_at' => $currentTimestamp,
                    'verified_id' => $request->get('staff_id')
                ]);

                $dateDonation = $dateDonation->subHours(7);
            }

            $dateExpired = $dateDonation->copy();
            $dateExpired->addDays(5);

            $request->request->add([
                'unique_value' => Donation::getUniqueValue($request->get('donation')),
                'date_donation' => $dateDonation->toDateTimeString(),
                'expired_at' => $dateExpired->toDateTimeString()
            ]);

            $bank = MemberBank::find($request->get('bank_id'));

            if ((array_search($request->get('bank_id'), explode(',', env('NO_UNIQUE_VALUE', '0')))) || in_array($bank->bank_id, [142, 143])) {

                $request->request->add([
                    'unique_value' => 0
                ]);

            }

            if ($request->get('staff_id')) {

                $request->request->add([
                    'unique_value' => 0
                ]);

            }

            $donation = Donation::create($request->all());

            if ($request->get('payment_id') && $request->get('total_donation')) {

                $payment->update([
                    'claim_at' => $currentTimestamp,
                    'donation_id' => $donation->id
                ]);

            }


            if (!$request->get('staff_id')) {

                GuideDonation::dispatch($donation->id)->delay(now()->addSecond(1))->onQueue(env('APP_MEMBER'));

            }

            $donation = Donation::find($donation->id);

            $donor = User::find($donation->donor_id);

            $payloadUpdateDonorData = [];

            if ($request->get('address') && !$donor->address) {
                $payloadUpdateDonorData['address'] = $request->get('address');
            }

            if ($request->get('gender') && !$donor->gender) {
                $payloadUpdateDonorData['gender'] = $request->get('gender');
            }

            if ($request->get('work') && !$donor->work) {
                $payloadUpdateDonorData['work'] = $request->get('work');
            }

            if ($request->get('birthday') && !$donor->address) {
                $payloadUpdateDonorData['birthday'] = $request->get('birthday');
            }

            if ($request->get('registered_at') && !$donor->registered_at) {
                $payloadUpdateDonorData['registered_at'] = $request->get('registered_at');
            } elseif (!$request->get('registered_at') && !$donor->registered_at) {
                $payloadUpdateDonorData['registered_at'] = $donor->created_at;
            } elseif ($request->get('registered_at') && $donor->registered_at) {
                if (Carbon::parse($request->get('registered_at')) < $donor->registered_at) {
                    $payloadUpdateDonorData['registered_at'] = $request->get('registered_at');
                }
            }

            $donor->update($payloadUpdateDonorData);

            DB::commit();

            $bank = $donation->bank()->select('id', 'bank_number', 'bank_info', 'bank_account', 'bank_id')->first();
            $campaign = $donation->campaign()->select('campaign_title')->first();
            $donor = $donation->donor()->select('full_name', 'phone_number', 'email', 'id')->first();

            $responses = [
                'donation_number'   => $donation->donation_number,
                'id'                => $donation->id,
                'expired_at'        => $donation->expired_at->format('Y-m-d H:i:s'),
                'bank'              => $bank,
                'donation'          => $donation->donation,
                'unique_value'      => $donation->unique_value,
            ];

            if ($bank->bank->bank_name == 'Faspay') {
                $responses['faspay_redirect_url'] = $this->faspayPayment($donation, $bank->bank_number, $donor, $campaign);
                $responses['unique_value'] = 0;
            }

            if ($bank->bank_id == 142) {

                list($responses['snap_token'], $err) = $this->getSnapToken($donation, $bank->bank_number);

                if  ($err) {
                    Log::error($err);
                    throw new Exception($err);
                }
                $responses['unique_value'] = 0;
            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully create donation';
            $res['data'] = $responses;
            return response($res, 200);

        } catch (Exception $exception) {
            Log::debug('ini exeption');

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function show($id)
    {
        try {
            $donation = Donation::with(['campaign' => function($query) {
                $query->select('id','campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation');
            }, 'bank' => function($query) {
                $query->select('id', 'bank_info');
            }])->ofMember(env('APP_MEMBER'))->where('donation_number', $id)->firstOrFail();

            if ($donation->donor_email) {
                $donation->donor_email = substr_replace($donation->donor_email,'****',4,4);
            }

            if ($donation->donor_phone) {
                $donation->donor_phone = substr_replace($donation->donor_phone,'****',4,4);
            }

            $res = [
                'status' => 'success',
                'message' => 'Successfully get donation',
                'data' => $donation
            ];

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);


        }

    }

    public function showByCampaign(Request $request, $slug)
    {

        try {

            $donations = Donation::latest()->where('campaign_id', function ($query) use ($slug) {

                $query
                    ->select('id')
                    ->from('campaigns')
                    ->where('member_id', env('APP_MEMBER'))
                    ->where('campaign_slug', $slug)
                    ->first();

            });


            if ($request->get('status')) {

                $donations = $donations->whereNotNull('total_donation');

            }

            $offset = 15;

            if ($request->get('offset')) {
                $offset = $request->get('offset');
            }

            $donations = $donations->paginate($offset);

            $timezone = 7;
            if ($request->get('timezone')) {

                $timezone = (int)$request->get('timezone');

            }

            $donations = $donations->setCollection(

                $donations->getCollection()->map(function ($donation) use ($timezone) {

                    $donation->date_donation = Date::ChangeTimezone($timezone, $donation->date_donation);
                    $donation->expired_at = Date::ChangeTimezone($timezone, $donation->expired_at);

                    if ($donation->verified_at) $donation->verified_at = Date::ChangeTimezone($timezone, $donation->verified_at);
                    if ($donation->auto_verified_at) $donation->auto_verified_at = Date::ChangeTimezone($timezone, $donation->auto_verified_at);

                    if ($donation->donor_email) {
                        $donation->donor_email = substr_replace($donation->donor_email,'****',4,4);
                    }

                    if ($donation->donor_phone) {
                        $donation->donor_phone = substr_replace($donation->donor_phone,'****',4,4);
                    }

                    if ($donation->ovo_phone) {
                        $donation->ovo_phone = substr_replace($donation->ovo_phone,'****',4,4);
                    }

                    return $donation;
                })

            );

            $res['status'] = 'success';
            $res['message'] = 'Successfully get donation by campaign';
            $res['data'] = $donations;

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);


        }

    }

    public function verification(Request $request, $id)
    {

        try {

            $validator = Validator::make($request->all(), [
                'total_donation' => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            DB::beginTransaction();

            $donation = Donation::findOrFail($id);
            $verifiedAt = Carbon::parse($request->get('verified_at'))->subHours(7);

            $donation->update([
                'verified_at' => $verifiedAt,
                'verified_id' => Auth::id(),
                'total_donation' => $request->input('total_donation')
            ]);

            Payment::create([
                'member_id' => env('APP_MEMBER'),
                'donation_id' => $donation->id,
                'total_payment' => $request->input('total_donation'),
                'bank_id' => $donation->bank_id,
                'payment_at' => $verifiedAt,
                'claim_at' => $verifiedAt,
                'created_id' => Auth::id()
            ]);

            // // Update status NTT
            // QurbanOrder::where('donation_id', $donation->id)->where('qurban_location_id', 1)
            //     ->update([
            //         'qurban_status' => 'Belum Dipotong'
            //     ]);

            // // Update status non NTT
            // QurbanOrder::where('donation_id', $donation->id)->where('qurban_location_id', '!=', 1)
            //     ->update([
            //         'qurban_status' => 'Siap Dipotong'
            //     ]);

            if (!$donation->staff_id) {
                SendDonationReceived::dispatch($donation)->delay(now()->addSecond(1))->onQueue(env('APP_MEMBER'));
            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully verified donation';
            $res['data'] = '';
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 400);

        }

    }

    public function cancelVerification($id)
    {
        try {

            DB::beginTransaction();

            $donation = Donation::findOrFail($id);
            $donation->update([
                'verified_at' => null,
                'verified_id' => null,
                'total_donation' => null
            ]);

            $payment = Payment::where('donation_id', $id);
            $payment->update([
                'donation_id' => null,
                'payment_at' => null
            ]);

            QurbanOrder::where('donation_id', $id)->update([
                'qurban_status' => 'Awaiting Payment'
            ]);

            $res['status'] = 'success';
            $res['message'] = 'Successfully verified donation';
            $res['data'] = [
                'donation' => $donation,
                'payment' => $payment->get()
            ];
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';

            return response($res, 200);
        }
    }

    public function donationsHistory()
    {
        $user = Auth::user();
        $donations = $user->donations()->orderBy('id', 'desc')->paginate(12);
        $donations->transform(function($item){
            $campaign = $item->campaign;

            $status = '';
            if ($item->total_donation != null) {
                $status = 'Donasi Diterima';
            }

            if ($item->total_donation == null) {
                if (strtotime($item->expired_at) < strtotime(date('Y-m-d H:i:s'))) {
                    $status = 'Donasi Dibatalkan';
                }else{
                    $status = 'Menunggu Pembayaran';
                }
            }

            $campaign_name = $campaign ? $campaign->campaign_title : $item->type_donation;
            $campaign_image = $campaign ? $campaign->campaign_image : 'dd-campaigncard.jpg';

            $category = null;
            if ($campaign) {
                $category = $campaign->category ? $campaign->category->category_name : null;
            }

            return [
                'id'                => $item->donation_number,
                'campaign_name'     => $campaign_name,
                'campaign_image'    => $campaign_image,
                'donation_amount'   => $item->donation,
                'category'          => $category,
                'status'            => $status
            ];
        });

        return response()->json([
            'status'    => 'success',
            'message'   => null,
            'data'      => $donations
        ]);
    }

    public function createReceipt($donation_number)
    {
        try {
            $donation = Donation::whereDonationNumber($donation_number)->firstOrFail();
            $program = $donation->campaign ? $donation->campaign->campaign_title : $donation->type_donation;
            $payment_method = $donation->bank ? $donation->bank->bank_info : '';

            $path = 'attachments/donations/receipt';
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            switch (env('APP_MEMBER')) {
                case 'insanbumimandiri.org':
                    $filename = 'ibm.png';
                    break;

                case 'rumahasuh.org':
                    $filename = 'ra.png';
                    break;

                case 'pesantrenquran.org':
                    $filename = 'pqt.jpg';
                    break;

                case 'bantutetangga.com':
                    $filename = 'bantet.jpg';
                    break;

                case 'rumahpangan.org':
                    $filename = 'rpb.png';
                    break;

                default:
                    $filename = 'ibm.png';
                    break;
            }

            $img = Image::make(public_path('assets/images/' . $filename));

            $img->text($donation_number, 170, 1020, function($font) {
                $font->file(public_path('assets/arial.ttf'));
                $font->size(32);
            });

            $img->text($program, 170, 1160, function($font) {
                $font->file(public_path('assets/arial.ttf'));
                $font->size(32);
            });

            $img->text('Rp ' . number_format($donation->donation), 170, 1290, function($font) {
                $font->file(public_path('assets/arial.ttf'));
                $font->size(32);
            });

            $img->text(date('d F Y H:i:s', strtotime($donation->date_donation . ' + 7 hours')) . ' WIB', 1300, 1020, function($font) {
                $font->file(public_path('assets/arial.ttf'));
                $font->align('right');
                $font->size(32);
            });

            $img->text($payment_method, 1300, 1290, function($font) {
                $font->file(public_path('assets/arial.ttf'));
                $font->align('right');
                $font->size(32);
            });

            $img->save(public_path($path . '/receipt.jpg'));

            return response()->download($path . '/receipt.jpg');
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage(),
                'data'      => null
            ], 404);
        }
    }

    public function donationGuide(Request $request)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'donation_number'   => 'required',
                'signature'         => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $donation = Donation::where('donation_number', $request->donation_number)->firstOrFail();

            $characters = $donation->donation_number . $donation->donation . $donation->id;
            $signature = hash('sha512', $characters);

            if ($request->signature != $signature) {
                throw new Exception('Wrong signature');
            }

            $guide = new GuideDonation($donation->id);
            $guide->handle();

            $res['status'] = 'success';
            $res['message'] = 'Guide send';
            $res['data'] = null;

            return response($res, 200);

        } catch (Exception $exception) {
            Log::debug('ini exeption');

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function donationNotification(Request $request)
    {
        $donation_number = $request->donation_number;
        $type = $request->type;

        try {
            $donation = Donation::where('donation_number', $donation_number)->firstOrFail();
        } catch (Exception $e) {
            return 'invalid donation';
        }

        $campaignTitle = $donation->campaign_id ? $donation->campaign->campaign_title : $donation->type_donation;
        $totalDonation = number_format($donation->donation + $donation->unique_value, 0, '', '.');;
        $bank = $donation->bank;
        $paymentMethod = $bank->bank_info . " an. " . $bank->bank_account . " " . $bank->bank_number;
        $expiredAt = $donation->expired_at->addHours(7)->format('d M Y H.i');
        $dateDonation = $donation->date_donation->addHours(7)->format('Y m d H:i');
        try {
            switch ($type) {
                case 'success':
                    $success = new SendDonationReceived($donation);
                    $success->sendMessage($donation->donor_phone, $donation->donor_name, $campaignTitle, $totalDonation, $donation->donation_number);
                    break;

                case 'guide':
                    $guide = new GuideDonation($donation->id);
                    $guide->sendSMS($donation, $totalDonation, $paymentMethod, $campaignTitle, $expiredAt);
                    break;

                case 'reminder':
                    $reminder = new ReminderDonation();
                    $reminder->sendSMS($donation, $dateDonation, $totalDonation, $campaignTitle, $paymentMethod, $expiredAt);
                    break;

                default:
                    # code...
                    break;
            }

            $res['status'] = 'success';
            $res['message'] = 'notification send';
            $res['data'] = null;

            return response($res, 200);
        } catch (Exception $exception) {
            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);
        }
    }
}
