<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Donation;
use App\Jobs\PushDonationToBalans;

class BalansDisbursement implements ToCollection
{
	protected $date;
    protected $type;

    public function __construct($date, $source)
    {
        $this->date = $date;
        $this->source = $source;
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        foreach ($collection as $key => $value) {
        	if ($key > 0) {
        		$donation_number = null;

        		switch ($this->source) {
        			case 'midtrans':
        				$donation_number = $value[3];
        				$totalDonation = $value[13];
        				break;

        			case 'faspay':
        				$donation_number = $value[15];
                        $totalDonation = $value[13];
        				break;

        			case 'ovo':
        				$donation_number = substr($value[9],-10);
        				break;

        			default: break;
        		}

                $donation = Donation::where('donation_number', $donation_number)->firstOrFail();

        		$donation->total_donation = $totalDonation;

        		if ($donation) {
                    PushDonationToBalans::dispatch($donation, $this->date)
                        ->delay(now()->addSecond(1))->onQueue(env('APP_MEMBER'));
                }
        	}
        }
    }
}
