<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Models\WhatsappJob;
use App\Utils\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReminderDonation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder-donation';

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
     * @return void
     */
    public function handle()
    {
        if (env('REMINDER_DONOR', false)) {

            $donations = Donation::whereNull('reminder_at')->where('date_donation', '<', now()->subDays(3))
                ->where('expired_at', '>', now())->whereNull('total_donation')
                ->whereNotIn('bank_id', explode(',', env('SKIP_REMINDER', '0')))
                ->where('type_donation', '!=', 'Kurban Web 2021')
                ->whereNull('total_donation')
                ->whereNull('staff_id')->get();

            foreach ($donations as $donation) {
                $bank = $donation->bank;

                $campaignTitle = $donation->campaign_id ? $donation->campaign->campaign_title : $donation->type_donation;
                $dateDonation = $donation->date_donation->addHours(7)->format('Y m d H:i');
                $totalDonation = number_format($donation->donation + $donation->unique_value, 2, ',', '.');
                $paymentMethod = "$bank->bank_info $bank->bank_number an. $bank->bank_account";
                $expiredAt = $donation->expired_at->addHours(7)->format('d M Y H.i');

                if ($donation->donor_phone) {
                    $whatsapp_url = env('WHATSAPP_URL');
                    $whatsapp_worker = env('WHATSAPP_WORKER');
                    $donation->update([
                        'reminder_at' => now()
                    ]);
                    if ($whatsapp_url && $whatsapp_worker) {
                        try {
                            $this->sendWhatsapp($donation, $dateDonation, $totalDonation, $campaignTitle, $paymentMethod, $expiredAt);

                        } catch (Exception $e) {
                            $this->sendSMS($donation, $dateDonation, $totalDonation, $campaignTitle, $paymentMethod, $expiredAt);
                        }
                    }else{
                        $this->sendSMS($donation, $dateDonation, $totalDonation, $campaignTitle, $paymentMethod, $expiredAt);
                    }
                }

                if ($donation->donor_email) {
                    $this->sendEmail($donation);
                }
            }
        }
    }

    public function sendSMS($donation, $dateDonation, $totalDonation, $campaignTitle, $paymentMethod, $expiredAt)
    {
        switch (env('APP_MEMBER')) {
            case 'insanbumimandiri.org':
                $messageText = "Salam {$donation->donor_name}
Untuk donasi Bapak/Ibu pada tanggal {$dateDonation} nominal {$totalDonation} program {$campaignTitle} Apakah ada kesulitan dalam prosesnya ?
informasi lebih lanjut melalui WA wa.me/628122145114";
                break;
            case 'rumahasuh.org':
                $messageText = "Salam $donation->donor_name
Untuk donasi Bapak/Ibu pada tanggal $dateDonation nominal $totalDonation program $campaignTitle.
Apakah ada kesulitan dalam prosesnya? Informasi lebih lanjut melalui WA wa.me/628112220118";
                break;

            case 'pesantrenquran.org':
                $messageText = "Terimakasih Bapak/Ibu {$donation->donor_name}, atas niat baiknya menyalurkan donasi melalui Pesantren Quran Taqwa.
Untuk melanjutkan donasi harap transfer {$totalDonation}, ke rek {$donation->bank->bank_info} an {$donation->bank->bank_account} {$donation->bank->bank_number} untuk program {$campaignTitle} sebelum {$expiredAt}. ";
                break;
            default:
                $messageText = "Assalamu'alaikum Bapak/Ibu {$donation->donor_name} \n\n
                Terima kasih sudah berkenan mengunjungi website resmi kami harapandhuafa.org
                Kami melihat niat baik Bapak/Ibu mengunjungi website kami untuk: {$campaignTitle} pada {$dateDonation} dengan donasi sebesar {$totalDonation} \n\n
                Untuk melanjutkan donasi harap transfer {$totalDonation}, ke rek {$donation->bank->bank_info} an {$donation->bank->bank_account} {$donation->bank->bank_number} untuk program {$campaignTitle} sebelum {$expiredAt}. \n\n
                Jika sudah berdonasi, jangan lupa untuk konfirmasi ke nomor ini wa.me//6282114249965 \n\n
                Terima kasih, semoga Allah melimpahkan rezeki untuk Bapak/Ibu dan keluarga.";
                // Log::debug("Reminder donation on " . env('APP_MEMBER'));
                break;
        }

        Message::send($messageText, $donation->donor_phone);
    }

    public function sendWhatsapp($donation, $dateDonation, $totalDonation, $campaignTitle, $paymentMethod, $expiredAt)
    {
        $whatsapp_message = config('content.whatsapp_message');
        if (array_key_exists(env('APP_MEMBER'), $whatsapp_message)) {
            $reminder = config('content.whatsapp_message')[env('APP_MEMBER')]['reminder'];
            $reminder = str_replace('[name]', $donation->donor_name, $reminder);
            $reminder = str_replace('[date_donation]', $dateDonation, $reminder);
            $reminder = str_replace('[donation_number]', $donation->donation_number, $reminder);
            $reminder = str_replace('[amount]', $totalDonation, $reminder);
            $reminder = str_replace('[campaign]', $campaignTitle, $reminder);
            $reminder = str_replace('[bank]', $paymentMethod, $reminder);
            $reminder = str_replace('[expired]', $expiredAt, $reminder);

            Message::sendWhatsappMessage($donation->donor_phone, 'reminder', $reminder, $donation->donation_number);
        }
    }

    public function sendEmail($donation)
    {
        $data = array(
            "donation" => $donation
        );

        $fileName = 'emails.' . explode( ".", env('APP_MEMBER'))[0] . '.reminder-donation';

        $donation->update([
            'reminder_at' => now()
        ]);

        $member = env('APP_MEMBER');
        $contents = config('content.guide_email_content');

        if (array_key_exists($member, $contents)) {
            $content = $contents[$member];
            $content['member'] = $member;
            $content['donation'] = $donation;

            $fileName = 'emails.donation_reminder';
            $data = $content;
        }

        Mail::send($fileName, $data, function ($message) use ($donation) {
            $message->to($donation->donor_email, $donation->donor_name)
                ->subject("Pengingat Donasi");
            $message->from(env('MAIL_SENDER'), env('EMAIL_NAME'));
        });
    }
}
