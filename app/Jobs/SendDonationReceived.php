<?php

namespace App\Jobs;

use App\Models\Donation;
use App\Models\WhatsappJob;
use App\Utils\Message;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDonationReceived implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $donation;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function sendQurbanNotification($donation)
    {

        try {

            if (env('QURBAN_API_URL', false)) {

                $qurban_api_url = env('QURBAN_API_URL');

                $client = new Client();

                $client->request('POST', "{$qurban_api_url}/api/guest/qurban-order/claim", [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'code'          => $donation->donation_number,
                        'signature'     => hash('sha512', "{$donation->id}{$donation->donation_number}{$donation->total_donation}")
                    ]
                ]);
            }

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

    }

    public function handle()
    {
        try {
            $donation = $this->donation;

            $campaignTitle = $donation->campaign_id ? $donation->campaign->campaign_title : '';

            if ($donation->type_donation != 'Kurban Web 2021') {
                if ($donation->donor_email && !$donation->email_sent_at && $donation->total_donation && env('SEND_EMAIL')) {
                    $this->sendEmail($donation);
                }

                if ($donation->donor_phone && $donation->total_donation && env('SEND_WHATSAPP') && !$donation->whatsapp_sent_at) {

                    // WhatsappJob::firstOrCreate(
                    //     [
                    //         'job_type' => 'Confirmation',
                    //         'donation_id' => $donation->id,
                    //         'whatsapp_number' => $donation->donor_phone,
                    //         'worker' => env('WHATSAPP_WORKER', 'info-ibm')
                    //     ],
                    //     [
                    //         'job_status' => 'On Queue',
                    //         'priority' => 2,
                    //         'whatsapp_name' => $donation->donor_name,
                    //         'worker_mode' => 'anon',
                    //     ]
                    // );

                    // $donation->update([
                    //     'whatsapp_sent_at' => now()
                    // ]);

                }

               if ($donation->donor_phone && !$donation->whatsapp_sent_at && !$donation->message_sent_at && $donation->total_donation) {
                    $whatsapp_url = env('WHATSAPP_URL');
                    $whatsapp_worker = env('WHATSAPP_WORKER');

                    if ($whatsapp_url && $whatsapp_worker) {
                        try {
                            $this->sendWhatsapp($donation->donor_phone, $donation->donor_name, $campaignTitle, number_format($donation->total_donation,0,",","."), $donation->donation_number);
                            $donation->update([
                                'whatsapp_sent_at'  => now()
                            ]);
                        } catch (Exception $e) {
                            $this->sendMessage($donation->donor_phone, $donation->donor_name, $campaignTitle, number_format($donation->total_donation,0,",","."), $donation->donation_number);
                            $donation->update([
                                'message_sent_at'   => now(),
                            ]);
                        }
                    }else {
                        $this->sendMessage($donation->donor_phone, $donation->donor_name, $campaignTitle, number_format($donation->total_donation,0,",","."), $donation->donation_number);
                        $donation->update([
                            'message_sent_at'   => now(),
                        ]);
                    }
                }

                if (!$donation->donor_type) {
                    $donor = $donation->donor;
                    $donorType = 'New';

                    if ($donor->registered_at->firstOfMonth()->diffInMonths(now()->firstOfMonth())) {
                        $donorType = 'Existing';
                    }

                    $donation->update([
                        'donor_type' => $donorType
                    ]);
                }
            } else {
                $this->sendQurbanNotification($donation);
            }

        } catch (Exception $exception) {

            Log::error('Send Donation Received. ' . $exception->getMessage(), [
                'donation_id' => $donation->id
            ]);

        }

    }

    public function sendEmail($donation)
    {
        $member = env('APP_MEMBER');
        $contents = config('content.guide_email_content');

        $subject = 'Jazaakallah ' . $donation->donor_name . ' atas titipan donasi Anda';
        $to_name = $donation->donor_name;
        $to_email = $donation->donor_email;

        if (array_key_exists($member, $contents)) {
            $content = $contents[$member];
            $content['member'] = $member;
            $content['donation'] = $donation;
            $content['campaign_name'] = $donation->campaign ? $donation->campaign->campaign_title : $donation->type_donation;

            $this->donation->update([
                'email_sent_at' => now()
            ]);

            Mail::send('emails.donation_complete', $content, function ($message) use ($to_name, $to_email, $subject) {
                $message->to($to_email, $to_name)
                    ->subject($subject);
                $message->from(env('MAIL_SENDER'), env('EMAIL_NAME'));
            });
        }

        // $data = array(
        //         "donor_id" => $this->donation->donor_id,
        //         "donor_name" => $this->donation->donor_name,
        //         "date_donation" => Carbon::parse($this->donation->date_donation)->addHour(7),
        //         "campaign_name" => $this->donation->campaign ? $this->donation->campaign->campaign_title : $this->donation->type_donation,
        //         "total_donation" => $this->donation->total_donation,
        //         "donation" => $this->donation
        //     );

        //     $fileName = 'donation_complete.' . env('APP_MEMBER');
        //     $fileName = str_replace('.', '_', $fileName);

        //     Mail::send('emails.' . $fileName, $data, function ($message) use ($to_name, $to_email, $subject) {
        //         $message->to($to_email, $to_name)
        //             ->subject($subject);
        //         $message->from(env('MAIL_SENDER'), env('EMAIL_NAME'));
        //     });
    }

    public function sendWhatsapp($to_phone, $to_name, $campaign_title, $totalDonation, $donation_number)
    {
        $message = "Terima kasih banyak Bapak/Ibu {$to_name} atas donasi Anda untuk campaign {$campaign_title} dengan nominal Rp. {$totalDonation}. Semoga Bapak/Ibu {$to_name} selalu diberikan kesehatan, kemudahan, serta kelancaran di setiap urusan.";

        $whatsapp_message = config('content.whatsapp_message');
        if (array_key_exists(env('APP_MEMBER'), $whatsapp_message)) {
            $success = $whatsapp_message[env('APP_MEMBER')]['success'];
            $success = str_replace('[name]', $to_name, $success);
            $success = str_replace('[campaign]', $campaign_title, $success);
            $success = str_replace('[amount]', $totalDonation, $success);

            $message = $success;
        }

        Message::sendWhatsappMessage($to_phone, 'success', $message, $donation_number);
        $this->donation->update([
            'whatsapp_sent_at'  => now()
        ]);
    }

    public function sendMessage($to_phone, $to_name, $campaign_title, $totalDonation, $donation_number)
    {
        switch (env('APP_MEMBER')) {
            case 'kaunyberbagi.com':
                $messageText = 'Jazaakumullah khairan katsiran ' . $to_name . ' atas infak Anda sebesar Rp ' . $totalDonation .' untuk program ' . $campaign_title;
                break;

            case 'pesantrenquran.org':
                $messageText = 'Terima kasih Bapak/Ibu ' . $to_name . ' atas titipan donasi nya melalui Pesantren Quran Taqwa. Donasi Bapak/Ibu pada campaign ' . $campaign_title . ' dengan nominal Rp. '. $totalDonation .'sudah kami terima, jazaakumullah khairan.';
                break;

            case 'rumahasuh.org':
                $messageText = "Terima kasih Bapak/Ibu {$to_name} atas titipan donasinya melalui Rumah Asuh. Donasi pada program {$campaign_title} Ini dengan nominal Rp. {$totalDonation} sudah kami terima. Semoga Bapak/Ibu {$to_name} dilimpakan keberkahan dalam setiap urusan.";
                break;

            case 'bantutetangga.com':
                $messageText = "";
                break;

            default :
                $messageText = "Assalamu'alaikum Wr.Wb\n
                Jazaakumullah khairan katsiran Bapak/Ibu {$to_name} \n\n
                Donasi Bapak/Ibu untuk {$campaign_title} sebesar {$totalDonation} telah kami terima. \n\n
                Semoga Allah memberikan keberkahan dan kemudahan bagi Bapak/Ibu dan keluarga.
                Dan semoga segala hajat dikabulkan Allah SWT.
                Aamiin Allahumma Aamiin \n\n
                Salam hangat,\n
                Dian Hafitri \n
                Cust. Relationship harapandhuafa.org";
                break;
        }

        $this->donation->update([
            'message_sent_at'   => now(),
        ]);

        Message::send($messageText, $to_phone);
    }

}
