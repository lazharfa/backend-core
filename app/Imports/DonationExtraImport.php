<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\Campaign;
use App\Models\User;

class DonationExtraImport implements ToCollection
{
    protected $bank_id;
    protected $response;

    public function __construct($bank_id)
    {
        $this->bank_id = $bank_id;
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $member = env('APP_MEMBER');
        $donor = User::firstOrCreate([
            'full_name'     => 'Donatur Extra',
            'email'         => "extradonor@$member",
            'member_id'     => $member
        ]);
        foreach ($collection as $key => $value) {
            if ($key > 0) {
                $campaign = Campaign::whereCode((string)$value[1])->first();
                $date_donation = $value[2];
                if ($value[3]) {
                    $date_donation = $date_donation . ' ' . $value[3] . ':00';
                }

                $insert_data = [
                    'donor_name'    => $value[0],
                    'donation'      => $value[4],
                    'total_donation'=> $value[4],
                    'donor_email'   => $donor->email,
                    'donor_id'      => $donor->id,
                    'member_id'     => $member,
                    'date_donation' => $date_donation,
                    'verified_at'   => $date_donation,
                    'bank_id'       => $this->bank_id,
                    'expired_at'    => date('Y-m-d H:i:s', strtotime($date_donation)),
                    'unique_value'  => 0,
                ];

                if ($campaign) {
                    $insert_data['campaign_id'] = $campaign->id;
                }else{
                    $insert_data['type_donation'] = (string)$value[1];
                }

                $this->response = $insert_data;

                $donation = Donation::create($insert_data);

                $payment = Payment::create([
                    'member_id'         => $member,
                    'donation_id'       => $donation->id,
                    'total_payment'     => $value[4],
                    'bank_id'           => $this->bank_id,
                    'payment_at'        => $date_donation,
                    'claim_at'          => $date_donation,
                ]);
            }
        }
    }

    public function getResponse()
    {
        return $this->response;
    }
}
