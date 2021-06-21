<?php

namespace App\Http\Controllers;

use App\Imports\BroadcastImport;
use App\Imports\WhatsappJobImport;
use App\Models\Donation;
use App\Models\QurbanOrder;
use App\Models\WhatsappAttachment;
use App\Models\WhatsappJob;
use App\Models\WhatsappMessage;
use App\Models\WhatsappWorker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class WhatsappJobController extends Controller
{
    public function store(Request $request)
    {

        try {

            DB::beginTransaction();

            $auth = Auth::user();

            $whatsappJob = WhatsappJob::create([
                'job_status' => 'On Queue',
                'message' => $request->get('message'),
                'creator_id' => $auth->id
            ]);

            foreach ($request->get('numbers') as $item) {

                WhatsappMessage::create([
                    'whatsapp_job_id' => $whatsappJob->id,
                    'whatsapp_name' => $item['whatsapp_name'],
                    'whatsapp_number' => $item['whatsapp_number'],
                    'status' => 'On Queue',
                ]);

            }

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully verified donation';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'message' => 'Internal Server Error.',
                'data' => ''
            ], 200);

        }

    }

    public function storeWithFile(Request $request)
    {

        try {

            DB::beginTransaction();

            $whatsappMessage = null;

            if ($request->get('message')) {
                $whatsappMessage = WhatsappMessage::create([
                    'message' => $request->get('message')
                ]);
            }

            $file = $request->file('contacts');

            // File Details
            $extension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();

            // Valid File Extensions
            $valid_extension = array("xls", "xlsx", "csv");

            // 2MB in Bytes
            $maxFileSize = 2097152;

            // Check file extension
            if (!in_array(strtolower($extension), $valid_extension)) {
                throw new Exception('Invalid File Extension.');
            }

            // Check file size
            if ($fileSize > $maxFileSize) {
                throw new Exception('File too large. File must be less than 2MB.');
            }

            // File upload location
            $location = 'uploads';

            if ($request->file('attachments')) {

                foreach ($request->file('attachments') as $attachment) {

                    $attachmentExtension = $attachment->getClientOriginalExtension();
                    $attachmentName = time() . '-' . str_random(50) . '.' . $attachmentExtension;
                    $attachment->move($location, $attachmentName);

                    WhatsappAttachment::create([
                        'whatsapp_message_id' => $whatsappMessage->id,
                        'file_name' => $attachmentName
                    ]);

                }

            }

            $fileName = time() . "-" . rand() . "." .$extension;

            $file->move($location, $fileName);

            $filePath = public_path('uploads/'.$fileName);

            if (!$whatsappMessage) {
                $whatsappMessageId = null;
            } else {
                $whatsappMessageId = $whatsappMessage->id;
            }

            $priority = 4;

            if ($request->get('priority')) {
                $priority = $request->get('priority');
            }

            Excel::import(new WhatsappJobImport($request->get('worker'), $priority, $whatsappMessageId), $filePath);

            File::delete($filePath);

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Successfully create whatsapp job.',
                'data' => ''
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'data' => ''
            ], 200);

        }


    }

    public function getJob(Request $request)
    {
        try {

            DB::beginTransaction();

            $worker = $request->query('worker');

            WhatsappWorker::updateOrCreate(
                [
                    'worker_name' => $worker,
                ],
                [
                    'worker_status' => 'Running',
                    'last_check' => now(),
                    'next_check' => now()->addMinutes(5)
                ]
            );

            WhatsappJob::whereNotNull('job_start_at')->where('job_start_at', '<=', now()->subMinutes(30))->where('worker', $worker)->whereNull('job_end_at')->update([
                'job_status' => 'Failed',
                'job_end_at' => now()
            ]);

            $startHour =  15;

            if (now()->dayOfWeek == 5) {
                $startHour = 14;
            }

            if (env('WHATSAPP_MAINTENANCE', false)) {

                $whatsappJob = null;

            } elseif ((now()->hour >= $startHour and now()->hour < 23)) {

                $whatsappJob = WhatsappJob::with('message', 'message.attachments')
                    ->whereNull('job_start_at')->where('worker', $worker)
                    ->whereIn('priority', ['0', '1', '2'])->orderBy('priority')
                    ->orderBy('id')->first();

            } else {

                $whatsappJob = WhatsappJob::with('message', 'message.attachments')
                    ->whereNull('job_start_at')->where('worker', $worker)
                    ->orderBy('priority')->orderBy('id')->first();

            }

            if ($whatsappJob) {

                $whatsappJob->update([
                    'job_status' => 'On Worker',
                    'job_start_at' => now()
                ]);

                $donation = null;

                if ($whatsappJob->donation_id) {
                    $donation = Donation::find($whatsappJob->donation_id);
                    $campaignTitle = $donation->campaign_id ? $donation->campaign->campaign_title : $donation->type_donation;
                    $dateDonation = $donation->date_donation->addHours(7);
                    $totalDonation = number_format($donation->donation + $donation->unique_value, 2, ',', '.');
                    $bank = $donation->bank;
                    $paymentMethod = $bank->bank_info . " an " . $bank->bank_account . " " . $bank->bank_number;
                    $expiredAt = $donation->expired_at->addHours(7);
                }

                switch ($whatsappJob->job_type) {

                    case 'Confirmation':

                        if ($donation->type_donation == 'Qurban ' . date('Y')) {

                            $qurbanOrders = QurbanOrder::where('donation_id', $donation->id)->get();

                            $qurbanOrder = $qurbanOrders->first();

                            if ($qurbanOrders->count() > 1) {
                                $qurbanTypeCount = $qurbanOrders->filter(function($qurbanOrder) {

                                    return $qurbanOrder->parent_id == null;
                                })->count();

                                $qurbanType = "{$qurbanTypeCount} {$qurbanOrder->qurban_type}";
                                $qurbanNames = implode(", ", $qurbanOrders->pluck('qurban_name')->toArray());
                            } else {
                                $qurbanType = "1 {$qurbanOrder->qurban_type}";
                                $qurbanNames = $qurbanOrder->qurban_name;
                            }


                           $message_text =  "Jazaakumullah khairan katsiran *{$donation->donor_name}*, pemesanan kurban *{$qurbanType}*  sebesar Rp *{$totalDonation}* pada *{$dateDonation}* sudah kami terima atas nama :
*$qurbanNames*

اَ جَرَكَ اللهُ فِيْمَا اَ عْطَيْتَ، وَ بَا رَ كَ فِيْمَا اَ بْقَيْتَ وَ جَعَلَهٌ لَكَ طَهٌوْ رًا

Semoga Allah memberikan pahala pada apa yang diberikan, memberkahi dalam harta-harta yang masih tersisa, juga menjadikannya sebagai pembersih dosa.

Semoga kurban yang ditunaikan mengantarkan pada keluasan rezeki yang berkah, dikabulkan do’a, hajat, dan cita-citanya serta mendapatkan balasan yang lebih baik dari Allah Swt. Aamiin yaa Rabbal Aalamiin.

Official Customer Service
Chintya : wa.me/628122145114
Yeni : wa.me/6281324607225
Riri : wa.me/6289699001125

Salam Hangat,
*Tanti Isyka Rafatullah*
Customer Relation";

                        } else {

                            $message_text = "Jazaakumullah khairan katsiran *$donation->donor_name*, donasi sebesar Rp *{$totalDonation}* pada {$dateDonation} sudah kami terima dan tercatat untuk program: *" . $campaignTitle . "*\n\r\n\rاَ جَرَكَ اللهُ فِيْمَا اَ عْطَيْتَ، وَ بَا رَ كَ فِيْمَا اَ بْقَيْتَ وَ جَعَلَهٌ لَكَ طَهٌوْ رًا\n\r\n\rSemoga Allah memberikan pahala pada donasi yang diberikan dan semoga Allah memberkahi dalam harta-harta yang masih tersisa dan semoga pula menjadikannya sebagai pembersih dosa.\n\r\n\rSenantiasa diberi kesehatan, keluasan rizki yang berkah, dikabulkan do’a, hajat dan cita-citanya serta mendapatkan balasan yang lebih baik dari Allah Swt. Aamiin yaa Rabbal Aalamiin.\n\r\n\rOfficial Customer Service\nChintya : wa.me/628122145114\nYeni : wa.me/6281324607225\nRiri : wa.me/6289699001125\n\r\n\rSalam Hangat,\n\r*Tanti Isyka Rafatullah*\n\rCustomer Relation";

                        }


                        $whatsappJob->message_text = $message_text;

                        break;

                    case 'Reminder':


                        if ($donation->total_donation or $donation->expired_at < now()) {

                            $whatsappJob->update([
                                'job_end_at' => now(),
                                'job_status' => 'Expired'
                            ]);

                            $whatsappJob = null;
                            break;
                        }

                        if ($donation->type_donation == 'Qurban ' . date('Y')) {


                            $message_text = "Assalamu’alaikum Bapak/Ibu *{$donation->donor_name}*. Untuk pembayaran Program Kurban di Pedalaman
Pada tanggal *{$dateDonation}*
Nominal Rp {$totalDonation}
Metode pembayaran melalui

Apakah mengalami kesulitan dalam prosesnya?

Jika mengalami kesulitan dalam prosesnya, silakan menghubungi nomor berikut
Official Customer Service
Chintya : wa.me628122145114
Yeni : wa.me6281324607225
Riri : wa.me6289699001125

dengan senang hati akan membantu sehingga niat baik Bapak/Ibu *{$donation->donor_name}* untuk berkurban di pedalaman segera terwujud.

Salam hangat
Tanti lsyka Rafatullah
Insan Bumi Mandin";


                        } else {

                            $message_text = "Assalamualaikum Bapak/Ibu *{$donation->donor_name}*, untuk donasi
Pada tanggal {$dateDonation}
Program {$campaignTitle}
Nominal *Rp {$totalDonation}*
Metode pembayaran melalui {$paymentMethod}

Apakah ada kesulitan dalam prosesnya?

Silakan menghubungi nomor berikut

Official Customer Service
Chintya : wa.me/628122145114
Yeni : wa.me/6281324607225
Riri : wa.me/6289699001125

dengan senang hati akan membantu sehingga niat kebaikan Bapak/Ibu {$donation->donor_name} dapat segera terwujud.

Salam hangat
*Tanti Isyka Rafatullah*
Insan Bumi Mandiri";

                        }

                        $whatsappJob->message_text = $message_text;

                        break;

                    case 'Guide':

                        if ($donation->total_donation != null) {

                            $whatsappJob->update([
                                'job_end_at' => now(),
                                'job_status' => 'Expired'
                            ]);

                            $whatsappJob = null;
                            break;
                        }

                        if ($donation->type_donation == 'Qurban ' . date('Y')) {

                            $qurbanOrders = QurbanOrder::where('donation_id', $donation->id)->get();

                            if ($qurbanOrders->count() > 1) {
                                $qurbanNames = implode(", ", $qurbanOrders->pluck('qurban_name')->toArray());
                            } else {
                                $qurbanNames = $qurbanOrders[0]->qurban_name;
                            }

                            $qurbanOrder = $qurbanOrders->first();

                            $message_text = "*Konfirmasi Pembayaran Kurban*
Assalamualaikum Bapak/Ibu *{$donation->donor_name}*

Terima kasih sudah melakukan pemesanan Kurban melalui Insan Bumi Mandiri.

Detail Pesanan :
- Nama Pemesan : {$donation->donor_name}
- Nama Pekurban : {$qurbanNames}
- Jenis Hewan : {$qurbanOrder->qurban_type}
- Daerah Pemotongan : {$qurbanOrder->qurban_location}
- Nominal Kurban : *Rp {$totalDonation}*
(mohon untuk melakukan transfer sesuai nominal yang tertera/menyertakan kode unik)
- Metode Pembayaran : {$paymentMethod}

Selangkah lagi untuk melayarkan kurban Bapak/Ibu ke pedalaman. Silakan melakukan transfer sebelum *{$expiredAt}*. Semoga kurban Bapak/Ibu menjadi berkah dan manfaat serta diganti dengan rezeki yang berkah berlimpah. Aamiin.

Official Customer Service
Riri : wa.me/6289699001125
Chintya : wa.me/628122145114
Yeni : wa.me/6281324607225

Salam Hangat
*Tanti Isyka Rafatullah*
Insan Bumi Mandiri";
                        } else {

                            $message_text = "*Konfirmasi Donasi*
Assalamualaikum Bapak/Ibu *{$donation->donor_name}*,

Terimakasih sudah melakukan donasi melalui Insan Bumi Mandiri..

Detail Donasi :
- Pilihan Program : {$campaignTitle}
- Tanggal Donasi : {$dateDonation}
- Nominal Donasi : *Rp {$totalDonation}*
(mohon untuk melakukan transfer donasi sesuai nominal yang tertera/menyertakan kode unik)
- Metode Pembayaran : {$paymentMethod}

Mari selangkah lagi untuk mewujudkan kebaikan Bapak/Ibu. Silahkan melakukan transfer donasi sebelum {$expiredAt}. Semoga semua kebaikan Bapak/Ibu diganti dengan rejeki yang berkah berlimpah. Aamiin.

Official Customer Service
Riri : wa.me/6289699001125
Chintya : wa.me/628122145114
Yeni : wa.me/6281324607225

Salam Hangat
*Tanti Isyka Rafatullah*
Insan Bumi Mandiri";

                        }

                        $whatsappJob->message_text = $message_text;

                        break;

                    case 'Report Qurban':

                        $qurbanOrder = QurbanOrder::findOrFail($whatsappJob->qurban_order_id);

                        $donation = Donation::find($qurbanOrder->donation_id);

                        $urlReport = "https://storage.insanbumimandiri.org/qurban/report/{$qurbanOrder->id}";

                        $message_text = "*LAPORAN KURBAN DI PEDALAMAN INSAN BUMI MANDIRI*

Assalamu'alaikum Bapak/Ibu {$donation->donor_name} terimakasih atas kepercayaannya dan amanah yang diberikan kepada Insan Bumi Mandiri dalam menyalurkan kurban Bapak/Ibu {$donation->donor_name} tahun ini

Alhamdulillah atas dukungan doa Bapak/Ibu semuanya, tahun ini kami bisa memberikan bantuan kepada 16.000 keluarga di seluruh wilayah pedalaman Papua Barat, NTT, NTB, Sumatera Selatan, dan Sulawesi Tengah.

Untuk laporan berupa video, tim kami di lapangan sudah berusaha melakukan pengambilan video penyembelihan dan penyaluran hewan kurban Bapak/Ibu {$donation->donor_name} Namun di luar prediksi kami terjadi sedikit kendala. _Hasil videonya tidak dapat terbuka (file trouble)._

Mohon maaf sekali atas ketidaknyamanan ini Bapak/Ibu {$donation->donor_name}, dengan sangat berat hati laporan berupa video tidak dapat kami sampaikan. 🙏🏻

Semoga hal ini tidak mengurangi sedikitpun kepercayaan Bapak/Ibu {$donation->donor_name} kepada Insan Bumi Mandiri, insya Allah hal ini langsung menjadi *fokus perbaikan kami dalam implementasi Kurban selanjutnya, di tahun 1442 H. Untuk melakukan double back up pengambilan dokumentasi video di lapangan*, sebagai antisipasi apabila terjadi hal yang sama 🙏🏻

*Namun alhamdulillah kami masih memiliki back up dokumentasi berupa foto untuk proses penyaluran.*

Berikut kami sampaikan laporan kurban atas nama : {$qurbanOrder->qurban_name}

$urlReport

Di dalam dokumentasi foto, Bapak/Ibu bisa melihat betapa lebarnya senyum kebahagiaan mereka sebagai ungkapan tulus terima kasih dan doa terbaik untuk Bapak/Ibu sekeluarga.

Kami berharap sinergi kebaikan ini akan terus berlanjut.

Demikian informasi dari kami, mengiringi rasa bahagia yang tak terkira dari saudara-saudara kita kaum muslimin di pedalaman Indonesia.

Salam Hangat,
Insan Bumi Mandiri";

                        $whatsappJob->message_text = $message_text;

                        break;

                    default:
                        break;
                }

            }

            DB::commit();

            $res = [
                'status' => 'success',
                'message' => 'Successfully get job',
                'data' => $whatsappJob
            ];

            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'data' => ''
            ], 200);

        }

    }

    public function reportJob(Request $request)
    {
        try {

            DB::beginTransaction();

            WhatsappWorker::updateOrCreate(
                [
                    'worker_name' => $request->get('worker')
                ],
                [
                    'worker_status' => 'Running',
                    'last_check' => now(),
                    'next_check' => now()->addMinutes(5)
                ]
            );

            $whatsappJob = WhatsappJob::findOrFail($request->get('id'));

            if ($whatsappJob) {

                $whatsappJob->update([
                    'job_status' => $request->get('job_status'),
                    'job_end_at' => $request->get('job_end_at')
                ]);

            }

            DB::commit();

            $res = [
                'status' => 'success',
                'message' => 'Successfully report job',
                'data' => $whatsappJob
            ];

            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'data' => ''
            ], 200);

        }
    }

    public function getAttachment(Request $request, $name)
    {

        $filepath = public_path("uploads/" . $name);

        if ($request->query('qurban') == "true") {
            $filepath = storage_path("app/public/qurban-report/images/$name");
        }

        if (!file_exists($filepath)) {
            return 'file not exist';
        }

        return response()->download($filepath);

    }

    public function cancelJob(Request $request)
    {
        $whatsapp_message_id = $request->whatsapp_message_id;
        $job_status = 'On Queue';
        $job_start_at = date('Y-m-d H:i:s');
        $job_end_at = date('Y-m-d H:i:s');

        $count_job_cancel = $whatsapp_jobs = WhatsappJob::where(compact('whatsapp_message_id', 'job_status'))->count();

        WhatsappJob::where(compact('whatsapp_message_id', 'job_status'))->update([
            'job_start_at'  => date('Y-m-d H:i:s'),
            'job_end_at'    => date('Y-m-d H:i:s'),
            'job_status'    => 'Cancel'
        ]);

        return response([
            'status' => 'success',
            'message' => $count_job_cancel . ' Job canceled',
            'data' => null
        ], 200);
    }

    public function injectBroadcast(Request $request)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'source_file' => 'required',
                'attachment' => 'required',
                'worker' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $sourceFile = $request->file('source_file');
            $attachment = $request->file('attachment');

            $location = 'uploads';

            $fileName = time() . "-" . rand() . "." .$sourceFile->getClientOriginalExtension();
            $sourceFile->move($location, $fileName);

            $attachmentName = time() . '-' . str_random(50) . '.' . $attachment->getClientOriginalExtension();
            $attachment->move($location, $attachmentName);

            Excel::import(new BroadcastImport($request->input('worker'), $attachmentName), public_path('uploads/'.$fileName));

            File::delete(public_path('uploads/'.$fileName));

            DB::commit();

            return response([
                'status' => 'success',
                'message' => "successfully inject broadcast",
                'data' => null
            ]);

        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'data' => null
            ], 400);
        }
    }
}
