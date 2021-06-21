<?php

use Illuminate\Database\Seeder;
use App\Models\Bank;
use App\Models\MemberBank;

class FaspayBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_member = env('APP_MEMBER');
        // $app_member = 'insanbumimandiri.org';

        $bank = Bank::firstOrCreate([
        	'bank_name'    => 'Faspay', 
            'bank_code'    => 'Faspay',
        ]);

        MemberBank::firstOrCreate([
            'bank_id'       => $bank->id,
            'member_id'     => $app_member, 
            'bank_account'  => 'Dana', 
            'bank_number'   => '819', 
            'bank_info'     => 'Dana',
            'bank_info_en'  => 'Dana'
        ]);

        MemberBank::firstOrCreate([
            'bank_id'       => $bank->id,
            'member_id'     => $app_member, 
            'bank_account'  => 'OVO', 
            'bank_number'   => '812', 
            'bank_info'     => 'OVO',
            'bank_info_en'  => 'OVO'
        ]);

        MemberBank::firstOrCreate([
            'bank_id'       => $bank->id,
            'member_id'     => $app_member, 
            'bank_account'  => 'LinkAja', 
            'bank_number'   => '302', 
            'bank_info'     => 'LinkAja',
            'bank_info_en'  => 'LinkAja'
        ]);
    }
}
