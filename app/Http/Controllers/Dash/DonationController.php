<?php

namespace App\Http\Controllers\Dash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\DonationsExport;
use App\Exports\DonationExtraTemplate;
use App\Imports\DonationExtraImport;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\MemberBank;
use App\Models\User;
use App\Transformers\Date;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\Transaction;
use App\Console\Commands\ReminderDonation;
use App\Traits\Helper;
use Illuminate\Validation\Rule;

class DonationController extends Controller
{
    use Transaction, Helper;

    public function donationImportTemplate()
    {
        return Excel::download(new DonationExtraTemplate, 'Template Import Donasi Extra.xlsx');
    }

    public function donationExtraImport(Request $request)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'file'      => 'required|file|mimes:xlsx',
                'bank_id'   => 'required|integer|exists:member_banks,id'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }
            $import = new DonationExtraImport($request->bank_id);
            Excel::import($import, $request->file('file'));

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Extra imported';
            $res['data'] = null;

            return response($res, 200);

        } catch (Exception $exception) {
            Log::debug('ini exeption');

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 400);
        }
    }

    public function donationExtraEditDonor(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'donation_number'      => 'required|exists:donations,donation_number',
                'full_name'     => 'required',
                'contact'       => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $contact = $request->contact;
            $donation_number = $request->donation_number;
            $full_name = $request->full_name;

            $type = $this->getContactType($contact);
            if ($type == null) {
                throw new Exception('No Hp/Email tidak valid');
            }

            $donation = Donation::where('donation_number', $donation_number)->first();
            $user = User::where('email', $contact)->orWhere('phone_number', $contact)->first();
            if (!$user) {
                $user = new User();
                $user->full_name = $full_name;
                if ($type == 'email') {
                    $user->email = $contact;
                }else{
                    $user->phone_number = $contact;
                }
                $user->member_id = env('APP_MEMBER');
                $user->save();

                $donation->donor_id = $user->id;
            }
            
            $donation->donor_id = $user->id;

            if ($type == 'email') {
                $donation->donor_email = $contact;
                $donation->donor_phone = null;
            }else{
                $donation->donor_phone = $contact;
                $donation->donor_email = null;
            }
            
            $donation->donor_name = $full_name;
            $donation->save();
            
            $res['status'] = 'success';
            $res['message'] = 'Donor updated';
            $res['data'] = null;

            return response($res, 200);

        } catch (Exception $exception) {
            Log::debug('ini exeption');

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 400);
        }
    }

    public function donationExtraEditCampaign(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'donation_number'       => 'required|exists:donations,donation_number',
                'campaign_id'           => Rule::requiredIf(!$request->type_donation && !$request->split_donation),
                'type_donation'         => Rule::requiredIf(!$request->campaign_id && !$request->split_donation),
                'split_donation'        => Rule::requiredIf(!$request->campaign_id && !$request->type_donation)
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $donation_number = $request->donation_number;
            $pray = $request->pray;
            $campaign_id = $request->campaign_id;
            $type_donation = $request->type_donation;
            $split_donation = $request->split_donation;

            $donation = Donation::where('donation_number', $donation_number)->first();
            $donation->pray = $pray;

            if ($split_donation) {
                if (count($split_donation) < 2) {
                    throw new Exception('Split minimal 2 row');
                }

                $donation->campaign_id = $split_donation[0]['campaign_id'];
                $donation->type_donation = $split_donation[0]['type_donation'];
                $donation->donation = $split_donation[0]['amount'];
                $donation->total_donation = $split_donation[0]['amount'];
                $member = env('APP_MEMBER');

                foreach ($split_donation as $key => $value) {
                    if ($key > 0) {
                        $childDonation = Donation::create([
                            'donor_name'    => $donation->donor_name,
                            'donation'      => $value['amount'],
                            'total_donation'=> $value['amount'],
                            'donor_email'   => $donation->donor_email,
                            'donor_phone'   => $donation->donor_phone,
                            'donor_id'      => $donation->donor_id,
                            'member_id'     => $member,
                            'date_donation' => $donation->date_donation,
                            'verified_at'   => $donation->verified_at,
                            'bank_id'       => $donation->bank_id,
                            'expired_at'    => $donation->expired_at,
                            'unique_value'  => 0,
                        ]);

                        $child_payment = Payment::create([
                            'member_id'         => $member,
                            'donation_id'       => $childDonation->id,
                            'total_payment'     => $value['amount'],
                            'bank_id'           => $donation->bank_id,
                            'payment_at'        => $donation->date_donation,
                            'claim_at'          => $donation->date_donation,
                        ]);
                    }
                }

                Payment::where('donation_id', $donation->id)->delete();
                Payment::create([
                    'member_id'         => $member,
                    'donation_id'       => $donation->id,
                    'total_payment'     => $split_donation[0]['amount'],
                    'bank_id'           => $donation->bank_id,
                    'payment_at'        => $donation->date_donation,
                    'claim_at'          => $donation->date_donation,
                ]);
            }else{
                $donation->campaign_id = $campaign_id;
                $donation->type_donation = $type_donation;
            }

            $donation->save();

            return response()->json([
                'status'    => 'error',
                'message'   => 'Campaign updated',
                'data'      => null
            ]);
        } catch (Exception $e) {
            return response([
                'status'    => 'error',
                'message'   => $e->getMessage(),
                'data'      => null
            ], 400);
        }
        
    }
}
