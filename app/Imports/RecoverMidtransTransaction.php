<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Donation;
use App\Models\Payment;
use Exception;
use Carbon\Carbon;

class RecoverMidtransTransaction implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
    	$executed = 0;
        foreach ($collection as $key => $row) {
        	if (($key > 0) && ($executed < 200)) {
        		if ($row[6] && ($row[4] == 'settlement')) {
        			try {
        				$field = strlen($row[0]) < 9 ? 'id' : 'donation_number';

	        			$donation = Donation::where($field, $row[0])->firstOrFail();
	        			if ($donation->total_donation == null) {
	        				$timeNow = now();

			                Payment::updateOrCreate(
			                    [
			                        'member_id' => env('APP_MEMBER'),
			                        'total_payment' => $row[3],
			                        'bank_id' => $donation->bank_id,
			                        'payment_at' => Carbon::parse(date('Y-m-d H:i:s', strtotime($row[6])))->subHour(7)
			                    ],
			                    [
			                        'description' => 'pay with ' . $row[1],
			                        'donation_id' => $donation->id,
			                        'claim_at' => $timeNow
			                    ]
			                );

			                $donation->update([
			                    'unique_value' => 0,
			                    'auto_verified_at' => $timeNow,
			                    'total_donation' => $row[3]
			                ]);

			                echo $row[0] . " claimed \n";

			                $executed++;
	        			}

	        			
	        		} catch (Exception $e) {
	        			echo$row[0] . " not exist \n";
	        		}
        		}	
        	}
        }

        echo "excuted " . $executed . "\n";
    }
}
