<?php

namespace App\Console\Commands;

use App\Models\CampaignNews;
use App\Models\Donation;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AddCampaignProgresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:add-progresses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $campaignProgress = CampaignNews::whereNull('sent_at')->whereNotNull('campaign_id')->orderBy('id')->first();

        if (env('TEST', false)) {

            $fileName = 'campaign_progress.' . env('APP_MEMBER');
            $fileName = str_replace('.', '_', $fileName);

            $donation = Donation::whereNotNull('total_donation')->where('campaign_id', $campaignProgress->campaign_id)->whereNotNull('donor_email')->first();


            $campaign_title = $donation->campaign->campaign_title;
            $to_email = 'singhamlagatari@insanbumimandiri.org';
            $subject = 'Progress ' . $campaign_title . ': ' . $campaignProgress->progress_title;
            $to_name = $donation->donor_name;

            $data = array(
                'campaignProgress' => $campaignProgress,
                'donation' => $donation
            );

            Mail::send('emails.campaign.' . $fileName, $data, function ($message) use ($to_name, $to_email, $subject) {
                $message->to($to_email, $to_name)->subject($subject);
                $message->from(env('MAIL_SENDER'), env('EMAIL_NAME'));
            });

        } elseif ($campaignProgress) {

            $campaignProgress->update([
                'sent_at' => date("Y-m-d H:i:s")
            ]);

            $donors = User::whereHas('donations', function($q) use ($campaignProgress){
                $q->where('campaign_id', $campaignProgress->campaign_id)
                    ->whereNotNull('total_donation')
                    ->whereNotNull('donor_email');
            })->get();

            $fileName = 'campaign_progress.' . env('APP_MEMBER');
            $fileName = str_replace('.', '_', $fileName);

            $fileName = 'emails.campaign.' . $fileName;

            foreach ($donors as $donor) {
                $donation = $donor->donation;

                $to_email = $donation->donor_email;
                $subject = 'Perkembangan dari Program yang Anda Bantu';
                $data = array(
                    'campaignProgress' => $campaignProgress,
                    'donation' => $donation
                );

                $member = env('APP_MEMBER');
                $contents = config('content.guide_email_content');
                
                if (array_key_exists($member, $contents)) {
                    $content = $contents[$member];
                    $content['member'] = $member;
                    $content['campaignProgress'] = $campaignNews;
                    $content['donor'] = $donor;

                    $fileName = 'emails.campaign_progress';
                    $data = $content;
                }

                $to_name = $donation->donor_name;

                Mail::send($fileName, $data, function ($message) use ($to_name, $to_email, $subject) {
                    $message->to($to_email, $to_name)->subject($subject);
                    $message->from(env('MAIL_SENDER'), env('EMAIL_NAME'));
                });
            }

        }


    }
}
