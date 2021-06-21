<?php

use Illuminate\Database\Seeder;
use App\Models\Campaign;

class InitTotalDonationPercentageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $campaigns = Campaign::where('total_fund', '>', 0)->get();

        foreach ($campaigns as $key => $value) {
        	$value->update([
        		'donation_percentage' => $value->total_fund / $value->target_donation * 100
        	]);
        }
    }
}
