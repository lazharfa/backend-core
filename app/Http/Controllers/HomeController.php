<?php

namespace App\Http\Controllers;

use App\Exports\DonationsExport;
use App\Exports\ExportPayments;
use App\Models\Campaign;
use App\Models\CampaignNews;
use App\Models\User;
use App\Models\Donation;
use App\Models\WhatsappHistories;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use App\Utils\Message;

class HomeController extends Controller
{
    public function chat()
    {
        $whatsappHistories = WhatsappHistories::select('whatsapp_number', DB::raw('count(id) count'))
            ->groupBy('whatsapp_number')
            ->orderByRaw('count(id)')
            ->first();

        $url = 'https://api.whatsapp.com/send?phone=' . $whatsappHistories->whatsapp_number;

        $campaignSlug = Input::get('campaign_slug');

        if ($campaignSlug) {

            $campaign = Campaign::where('campaign_slug', $campaignSlug)->orWhere('campaign_slug_en', $campaignSlug)->firstOrFail();

            if ($campaign) {
                $url = $url . '&text' . $campaign->campaign_title;
            }

        }

        WhatsappHistories::create([
            'member_id' => env('APP_MEMBER'),
            'whatsapp_number' => $whatsappHistories->whatsapp_number
        ]);

        return redirect($url);

    }

    public function index()
    {
        return redirect(env('APP_URL'));
    }

    public function home()
    {

        try {

            $campaigns = Campaign::ofMember(env('APP_MEMBER'))->where('campaign_status', 'Publish')->orderBy('priority')->latest()->limit(10)->get();
            $campaign_news = Campaign::ofMember(env('APP_MEMBER'))->latest()->limit(4)->get();


            $res = [
                'status' => 'success',
                'message' => 'Successfully get home data.',
                'data' => [
                    'campaigns' => $campaigns,
                    'campaign_news' => $campaign_news
                ]
            ];

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function exportPayment(Request $request)
    {
        $start_date = $request->get('start_date') ? $request->get('start_date') : date('Y-m-') . '01';
        $end_date = $request->get('end_date') ? $request->get('end_date') : date('Y-m-d');
        $fileName = $start_date. '-' . $end_date . '.xlsx';

        return Excel::download(new ExportPayments($start_date, $end_date), $fileName);

    }

    public function exportDonation(Request $request)
    {

        $fileName = $request->get('start_date') . '-' . $request->get('end_date') . '.xlsx';

        return Excel::download(new DonationsExport($request->get('start_date'), $request->get('end_date'), $request->get('str')), $fileName);

    }

    public function emailTest()
    {
        // $member = env('APP_MEMBER');
        // $donation = Donation::find(226692);

        // $member = 'rumahasuh.org';
        // $contents = config('content.guide_email_content');
        // $content = $contents[$member];
        // $content['member'] = $member;
        // $content['donation'] = $donation;

        // Mail::send('emails.guide_donation', $content, function ($message) {
        //     $message->to('rifkirahadian@adaide.co.id', 'Rifki');
        //     $message->subject('Test');
        // });

        $reminder = config('content.whatsapp_message')[env('APP_MEMBER')]['reminder'];
        $reminder = str_replace('[name]', 'Udin', $reminder);
        $reminder = str_replace('[date_donation]', 'now', $reminder);
        $reminder = str_replace('[donation_number]', '12091030', $reminder);
        $reminder = str_replace('[amount]', '400000', $reminder);
        $reminder = str_replace('[campaign]', 'Bantu udin', $reminder);
        $reminder = str_replace('[bank]', 'BCA', $reminder);
        $reminder = str_replace('[expired]', '27 sep 92', $reminder);

        Message::sendWhatsappMessage('6281299311170','Donation paid', $reminder);


        printf($reminder);


       

    }
}
