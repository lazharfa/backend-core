<?php

namespace App\Jobs;

use App\Models\Donation;
use App\Utils\Message;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GuideDonation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $donation = null;

    /**
     * Create a new job instance.
     *
     * @param $donationId
     *
     * @return void
     */
    public function __construct($donationId)
    {
        $this->donation = Donation::find($donationId);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $donation = $this->donation;
            $bank = $donation->bank;
            if (env('GUIDE_DONOR', false) && !in_array($bank->bank_id, [142, 143]))
            {
                $campaignTitle = $donation->campaign_id ? $donation->campaign->campaign_title : $donation->type_donation;
                $totalDonation = number_format($donation->donation + $donation->unique_value, 0, '', '.');;

                if ($donation->donor_phone) {

                    $expiredAt = $donation->expired_at->addHours(7)->format('d M Y H.i');
                    $paymentMethod = "$bank->bank_info $bank->bank_number an. $bank->bank_account";
                    $date_donation = $donation->date_donation->addHours(7)->format('d M Y H.i');

                    $whatsapp_url = env('WHATSAPP_URL');
                    $whatsapp_worker = env('WHATSAPP_WORKER');

                    if ($whatsapp_url && $whatsapp_worker) {
                        try {
                            $this->sendWhatsapp($donation, $totalDonation, $paymentMethod, $campaignTitle, $expiredAt, $date_donation);
                        } catch (Exception $e) {
                            $this->sendSMS($donation, $totalDonation, $paymentMethod, $campaignTitle, $expiredAt);
                        }
                    }else{
                        $this->sendSMS($donation, $totalDonation, $paymentMethod, $campaignTitle, $expiredAt);
                    }


                }

                if ($donation->donor_email) {
                    $this->sendEmail($donation);
                }
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function sendSMS($donation, $totalDonation, $paymentMethod, $campaignTitle, $expiredAt)
    {
        switch (env('APP_MEMBER')) {
            case 'insanbumimandiri.org':
                if ($donation->type_donation == 'Qurban ' . date('Y')) {
                    $messageText = "Terimakasih {$donation->donor_name}. Mohon transfer {$totalDonation} ke {$paymentMethod} untuk program kurban di pedalaman sebelum {$expiredAt}";
                } else {
                    $messageText = "Terimakasih {$donation->donor_name}. Mohon transfer {$totalDonation} ke {$paymentMethod} untuk program {$campaignTitle} sebelum {$expiredAt}";
                }
                break;

            case 'rumahasuh.org':
                $messageText = "Terima kasih Bapak/Ibu {$donation->donor_name}, atas niat baiknya untuk berbagi sesama melalui Rumah Asuh. Selangkah lagi untuk mengiringi perjuangan mereka. Kirim donasi sahabat melalui rekening {$paymentMethod} untuk program {$campaignTitle}, sebelum {$expiredAt} WIB";
                break;

            case 'pesantrenquran.org':
                $messageText = "Terimakasih {$donation->donor_name}. Mohon transfer {$totalDonation} ke {$donation->bank->bank_info} an {$donation->bank->bank_account} {$donation->bank->bank_number}  untuk program {$campaignTitle} sebelum {$expiredAt}.";
                break;
            default:
                $messageText = "Terimakasih {$donation->donor_name}. Mohon transfer {$totalDonation} ke {$paymentMethod} untuk program {$campaignTitle} sebelum {$expiredAt}";
                break;
        }

        Message::send($messageText, $donation->donor_phone);
    }

    public function sendWhatsapp($donation, $totalDonation, $paymentMethod, $campaignTitle, $expiredAt, $date_donation)
    {
        $whatsapp_message = config('content.whatsapp_message');
        if (array_key_exists(env('APP_MEMBER'), $whatsapp_message)) {
            $awaiting = config('content.whatsapp_message')[env('APP_MEMBER')]['awaiting'];
            $awaiting = str_replace('[name]', $donation->donor_name, $awaiting);
            $awaiting = str_replace('[date_donation]', $date_donation, $awaiting);
            $awaiting = str_replace('[donation_number]', $donation->donation_number, $awaiting);
            $awaiting = str_replace('[amount]', $totalDonation, $awaiting);
            $awaiting = str_replace('[campaign]', $campaignTitle, $awaiting);
            $awaiting = str_replace('[bank]', $paymentMethod, $awaiting);
            $awaiting = str_replace('[expired]', $expiredAt, $awaiting);

            Message::sendWhatsappMessage($donation->donor_phone, 'guide', $awaiting, $donation->donation_number);
        }
    }

    public function sendEmail($donation)
    {
        $memberName = explode( ".", env('APP_MEMBER'))[0];

        $data = array(
            "donation" => $donation
        );

        $fileName = "emails.$memberName.guide-donation";
        $subject = "Konfirmasi Donasi";

        $member = env('APP_MEMBER');
        $contents = config('content.guide_email_content');

        if (array_key_exists($member, $contents)) {
            $content = $contents[$member];
            $content['member'] = $member;
            $content['donation'] = $donation;

            $fileName = 'emails.guide_donation';
            $data = $content;
        }

        Mail::send($fileName, $data, function ($message) use ($donation, $subject) {
            $message->to($donation->donor_email, $donation->donor_name)
                ->subject($subject);
            $message->from(env('MAIL_SENDER'), env('EMAIL_NAME'));
        });
    }
}
