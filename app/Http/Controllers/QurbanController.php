<?php

namespace App\Http\Controllers;

use App\Jobs\GuideDonation;
use App\Models\Donation;
use App\Models\ImageFile;
use App\Models\Payment;
use App\Models\QurbanType;
use App\Models\QurbanLocation;
use App\Models\QurbanOrder;
use App\Models\QurbanPrice;
use App\Models\User;
use App\Models\WhatsappJob;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Spatie\Browsershot\Browsershot;
use PDF;

class QurbanController extends Controller
{
    public function dashboard()
    {
        try {

            $qurbanOrders = QurbanOrder::orderByDesc('date_donation')
                ->leftJoin('donations', 'qurban_orders.donation_id', '=', 'donations.id')
                ->select('qurban_orders.*')->whereNotNull('total_donation')->get();

            $notReadyToSend = $qurbanOrders->filter(function ($qurbanOrder) {
                return (count(collect($qurbanOrder->qurban_attachments)) != 3) || ($qurbanOrder->video_available_at == null);
            })->count();

            $readyToSend = $qurbanOrders->filter(function ($qurbanOrder) {
                return (count(collect($qurbanOrder->qurban_attachments)) == 3) && ($qurbanOrder->video_available_at != null) && $qurbanOrder->send_report_at == null;
            })->count();

            $sent = $qurbanOrders->filter(function ($qurbanOrder) {
                return $qurbanOrder->send_report_at != null;
            })->count();

            $imageUploaded = $qurbanOrders->filter(function ($qurbanOrder) {
                return count(collect($qurbanOrder->qurban_attachments)) == 3;
            })->count();

            $videoUploaded = $qurbanOrders->filter(function ($qurbanOrder) {
                return $qurbanOrder->video_available_at != null;
            })->count();

            $imageNotUploaded = $qurbanOrders->filter(function ($qurbanOrder) {
                return count(collect($qurbanOrder->qurban_attachments)) != 3;
            })->count();

            $videoNotUploaded = $qurbanOrders->filter(function ($qurbanOrder) {
                return $qurbanOrder->video_available_at == null;
            })->count();

            $res = [
                'status' => 'success',
                'message' => 'Successfully get report',
                'data' => [
                    'not_ready_to_send' => $notReadyToSend,
                    'ready_to_send' => $readyToSend,
                    'sent' => $sent,
                    'image_uploaded' => $imageUploaded,
                    'image_not_uploaded' => $imageNotUploaded,
                    'video_uploaded' => $videoUploaded,
                    'video_not_uploaded' => $videoNotUploaded,
                    'all' => $qurbanOrders->count()
                ]
            ];

            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function type()
    {
        try {

            $qurbanTypes = QurbanType::get();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get qurban locations';
            $res['data'] = $qurbanTypes;
            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function location()
    {
        try {

            $qurbanLocations = QurbanLocation::with('child:id,parent_id,location_name,location_slug', 'parent:id,location_name,location_slug')
                ->select('id', 'parent_id', 'location_name', 'location_slug','location_quota','location_cover','location_description')->orderBy('id');

            if (Input::get('parent')) {

                $qurbanLocations = $qurbanLocations->whereNull('parent_id');

            } elseif (Input::get('child')) {
                $qurbanLocations = $qurbanLocations->whereNotNull('parent_id');
            }

            if (Input::get('show_hide') != 'true') {
                $qurbanLocations = $qurbanLocations->where('location_status', 'Show');
            }

            $qurbanLocations = $qurbanLocations->get();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get qurban locations';
            $res['data'] = $qurbanLocations;
            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function locationQuota(Request $request)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'location_quota' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $qurbanLocation = QurbanLocation::findOrFail($request->get('id'));

            $qurbanLocation->update([
                'location_quota' => $request->get('location_quota')
            ]);

            DB::commit();

            return response([
                'status' => 'success',
                'messages' => 'Successfully Update quota location',
                'data' => $qurbanLocation
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function locationStatus()
    {
        try {

            $qurbanLocations = QurbanLocation::selectRaw('coalesce(qurban_locations.parent_id, qurban_locations.id) parent,
                   qurban_locations.location_name,
                   count(qurban_orders.id) filter ( where qurban_type_id = 1 ) kambing,
                   count(qurban_orders.id) filter ( where qurban_type_id = 2 ) kambing_super,
                   count(qurban_orders.id) filter ( where qurban_type_id = 3 ) domba,
                   count(qurban_orders.id) filter ( where qurban_type_id = 4 ) domba_super,
                   count(qurban_orders.id) filter ( where qurban_type_id = 5 ) sapi,
                   count(qurban_orders.id) filter ( where qurban_type_id = 6 ) "1/7sapi"'
            )
                ->leftJoin('qurban_orders', 'qurban_locations.id', '=', 'qurban_orders.qurban_location_id')
                ->leftJoin('donations', 'qurban_orders.donation_id', '=', 'donations.id')
                ->whereNotNull('donations.total_donation')->whereNull('qurban_orders.parent_id')
                ->groupBy('qurban_locations.id', 'qurban_locations.location_name')
                ->orderByRaw('parent, qurban_locations.id');

            if (Input::get('location')) {
                $qurbanLocations = $qurbanLocations->whereIn('qurban_locations.id', explode(',', Input::get('location')));
            }

            return response([
                'status' => 'success',
                'message' => 'Successfully get qurban status',
                'data' => $qurbanLocations->get()
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function locationDescription(Request $request)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'location_description' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $qurbanLocation = QurbanLocation::findOrFail($request->get('id'));

            $qurbanLocation->update([
                'location_description' => $request->get('location_description')
            ]);

            DB::commit();

            return response([
                'status' => 'success',
                'messages' => 'Successfully Update description location',
                'data' => $qurbanLocation
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function locationCover(Request $request)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'cover' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            if ($request->get('cover')) {

                $fileName = time() . '-' . str_random(50) . '.' . 'jpg';
                $resultImage = ImageFile::storeImage($request->get('cover'),$fileName);

                if ($resultImage) {
                    throw new Exception('Failed to save image. ' . $resultImage);
                }

                $request->request->add([
                    'location_cover' => $fileName
                ]);

            }

            $qurbanLocation = QurbanLocation::findOrFail($request->get('id'));

            $qurbanLocation->update([
                'location_cover' => $fileName
            ]);

            DB::commit();

            return response([
                'status' => 'success',
                'messages' => 'Successfully Update cover location',
                'data' => $qurbanLocation
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function price($slug)
    {

        try {

            $qurbanPrice = QurbanPrice::with('type:id,type_name', 'location:id,location_name')
            ->select('id', 'qurban_type_id', 'qurban_location_id', 'price');

            $qurbanPrice = $qurbanPrice->where('qurban_location_id', function ($query) use ($slug) {
                $query->from('qurban_locations')
                    ->select('id')
                    ->where('location_slug', $slug)->first();
            });

            if (Input::get('type')) {
                $qurbanPrice = $qurbanPrice->where('qurban_type_id', Input::get('type'));
            }

            $qurbanPrice = $qurbanPrice->get();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get qurban prices';
            $res['data'] = $qurbanPrice;
            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }

    }

    public function storeReportImage(Request $request){
        try{
            $data = $request->base64data;
            $qurbanOrder = QurbanOrder::findOrFail($request->get('id'));
            $donation = Donation::find($qurbanOrder->donation_id);
            $fileName = "{$donation->donation_number}-{$qurbanOrder->id}.jpg";
            $image = explode('base64',$data);
            file_put_contents(storage_path('app/public/qurban-report/images/') .$fileName, base64_decode($image[1]));


            $res['status'] = 'success';
            $res['message'] = 'Successfully store report image';
            $res['data'] = null;
            return response($res, 200);
        }catch (Exception $exception){

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = null;
            return response($res, 200);
        }
    }

    public function orderImport(Request $request)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'file_order' => 'required',
                'complete' => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $file = $request->file('file_order');

            // File Details
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();

            // 2MB in Bytes
            $maxFileSize = 2097152;

            // Check file extension
            if ($extension != 'csv') {
                throw new Exception('Invalid File Extension.');
            }

            if ($fileSize > $maxFileSize) {
                throw new Exception('File too large. File must be less than 2MB.');
            }

            $file = fopen($tempPath, "r");

            $importData_arr = array();
            $i = 0;

            $errorRequired = array();
            $columnNames = array();
            $entries = array();

            while (($fileData = fgetcsv($file, 1000, ",")) !== FALSE) {

                $num = count($fileData);
                for ($c = 0; $c < $num; $c++) {
                    $value = $fileData[$c];
                    $importData_arr[$i][$c] = $value;

                    if($i == 0){
                        // remove special character
                        $columnNames[$c] = preg_replace('/[^A-Za-z0-9\_]/', '', $importData_arr[0][$c]);
                    }else{
                        $columnName = $columnNames[$c];

                        if($value == "") {
                            $value = null;
                        }

                        if($columnName == 'qurban_type_id'){
                            if(!QurbanType::find($value)){
                                $errorRequired[] = strtoupper($columnName).' ('.$value.') Pada Baris '.$i.' tidak terdaftar';
                            }
                        }

                        if($columnName == 'qurban_location_id'){
                            if(!QurbanLocation::find($value)){
                                $errorRequired[] = strtoupper($columnName).' ('.$value.') Pada Baris '.$i.' tidak terdaftar';
                            }
                        }
                        $entries[$i][$columnName] = $value;

                        if($columnName == 'qurban_names'){
                            $qurbanNames= explode(';',$value);
                            $price = ((int) $entries[$i]['qurban_price']/(count($qurbanNames)));
                            $orders = array();
                            foreach ($qurbanNames as $key => $qurbanName){
                                $orders['qurban_name'] = trim($qurbanName);
                                $orders['qurban_price'] = $price;
                                $orders['qurban_type_id'] = $entries[$i]['qurban_type_id'];
                                $orders['qurban_location_id'] = $entries[$i]['qurban_location_id'];
                                $entries[$i]['orders'][$key] = $orders;
                            }
                            unset($entries[$i]['qurban_names']);
                            unset($entries[$i]['qurban_type_id']);
                            unset($entries[$i]['qurban_location_id']);
                            unset($entries[$i]['qurban_price']);
                        }

                    }
                }
                $i++;
            }
            fclose($file);

            if(count($errorRequired) != 0) {
                throw new Exception(implode("\r\n", $errorRequired));
            }

            $staff = User::where('email', $request->get('email'))->first();

            foreach ($entries as $key => &$entry) {

                $orders = $entry['orders'];
                $entry['date_donation'] = trim($entry['date_donation']);

                $addFields = array(
                    'member_id' => env('APP_MEMBER'),
                    'type_donation' => 'Qurban ' . date('Y'),
                    'expired_at' => now()->addDays(5),
                    'donation' => 0,
                    'unique_value' => 0,
                    'staff_id' => $staff->id
                );

                $entry = array_merge($entry, $addFields);

                $donation = Donation::create($entry);
                $donation = Donation::find($donation->id);
                $totalOrder = 0;
                $parent = null;

                foreach ($orders as $order) {

                    $qurbanPrice = $order['qurban_price'];
                    $qurbanName = $order['qurban_name'];
                    $qurbanTypeId = $order['qurban_type_id'];
                    $qurbanLocationId = $order['qurban_location_id'];

                    $qurbanOrder = QurbanOrder::create([
                        'parent_id' => $parent,
                        'donation_id' => $donation->id,
                        'donor_id' => $donation->donor_id,
                        'qurban_type_id' => $qurbanTypeId,
                        'qurban_location_id' => $qurbanLocationId,
                        'qurban_name' => $qurbanName,
                        'qurban_status' => 'Awaiting Payment',
                        'qurban_price' => $qurbanPrice
                    ]);

                    if (!$parent) {
                        $parent = $qurbanOrder->id;
                    }

                    $totalOrder += $price;

                }

                $update = [
                    'donation' => $totalOrder
                ];

                if ($request->get('complete') == 'true') {
                    $update['total_donation'] = $totalOrder;
                }

                $donation->update($update);
            }

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Successfully import data pequrban.',
                'data' => $entries
            ], 200);

        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'data' => ''
            ], 200);
        }
    }

    public function order(Request $request)
    {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'bank_id' => 'required',
                'donor_name' => 'required',
                'payment_id' => 'required_with:total_donation',
                'donor_email' => 'required_without:donor_phone|email|max:255',
                'donor_phone' => 'required_without:donor_email|min:9|regex:/^[0-9]+$/',
                'total_donation' => 'required_with:payment_id',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $orders = $request->get('orders');

            $payment = null;
            $currentTimestamp = date("Y-m-d H:i:s");

            if (!$request->get('date_donation')) {
                $request->request->add([
                    'date_donation' => now()
                ]);
            }

            $total_donation = $request->get('donation');

            $request->request->add([
                'member_id' => env('APP_MEMBER'),
                'type_donation' => 'Qurban ' . date('Y'),
                'expired_at' => '2020-08-02 05:00:00',
                'donation' => 0,
                'unique_value' => 0,
                'verified_at' => $currentTimestamp,
                'verified_id' => $request->get('staff_id')
            ]);

            if ($request->get('payment_id') && $request->get('total_donation')) {

                $payment = Payment::findOrFail($request->get('payment_id'));

                $request->request->add([
                    'date_donation' => $payment->payment_at,
                    'verified_at' => $currentTimestamp,
                    'verified_id' => $request->get('staff_id')
                ]);

            }

            $donation = Donation::create($request->all());
            $donation = Donation::findOrFail($donation->id);
            $totalOrder = 0;

            foreach ($orders as $order) {

                $parent = null;
                $qurbanPrice = QurbanPrice::findOrFail($order['price']);

                $price = $qurbanPrice->price;
                if($request->get('staff_id') && strtotime($request->get('date_donation')) < strtotime('2020-07-01 00:00:00')){
                    $price = $total_donation;
                }

                $qurbanNames = $order['qurban_names'];

                $price = $price/count($qurbanNames);


                foreach ($qurbanNames as $qurbanName) {

                    $qurbanOrder = QurbanOrder::create([
                        'parent_id' => $parent,
                        'donation_id' => $donation->id,
                        'donor_id' => $donation->donor_id,
                        'qurban_type_id' => $qurbanPrice->qurban_type_id,
                        'qurban_location_id' => $qurbanPrice->qurban_location_id,
                        'qurban_name' => $qurbanName,
                        'qurban_status' => 'Awaiting Payment',
                        'qurban_price' => $price
                    ]);

                    $totalOrder += $price;
                    if ($parent == null) {
                        $parent = $qurbanOrder->id;
                    }

                }

            }

            $uniqueValue = 0;

            if (!$request->get('staff_id')) {
                $uniqueValue = Donation::getUniqueValue($totalOrder);
            }

            $donation->update([
                'donation' => $totalOrder,
                'unique_value' => $uniqueValue,
                'total_donation' => $totalOrder
            ]);

            if ($request->get('payment_id') && $request->get('total_donation')) {

                $payment->update([
                    'claim_at' => $currentTimestamp,
                    'donation_id' => $donation->id
                ]);

            } elseif (!$request->get('staff_id') && env('SKIP_GUIDE', false) && !array_search($request->get('bank_id'), explode(',', env('SKIP_GUIDE', '0')))) {

                GuideDonation::dispatch($donation->id)->delay(now()->addSecond(1))->onQueue(env('APP_MEMBER'));

            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully qurban orders';
            $res['data'] = Donation::findOrFail($donation->id);
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }

    }

    public function orderList()
    {
        try {

            $qurbanOrders = QurbanOrder::orderByDesc('date_donation')
                ->leftJoin('donations', 'qurban_orders.donation_id', '=', 'donations.id')
                ->leftJoin('users as staffs', 'donations.staff_id', '=', 'staffs.id')
                ->leftJoin('users as checks', 'qurban_orders.check_id', '=', 'checks.id')
                ->select(
                    'qurban_orders.*',
                    'staffs.full_name as staff_name',
                    'checks.full_name as check_name',
                    'channel_source',
                    'channel_medium',
                    'channel_campaign',
                    'channel_term',
                    'channel_content',
                    'expired_at',
                    'donor_name',
                    'date_donation',
                    'donation',
                    'unique_value',
                    'total_donation'
                );

            if (Input::get('start_date') and Input::get('end_date')) {
                $qurbanOrders = $qurbanOrders->where('date_donation', '>=', Input::get('start_date'))
                    ->where('date_donation', '<=', Input::get('end_date'));
            }

            if (Input::get('location')) {
                $qurbanOrders = $qurbanOrders->where('qurban_location_id', Input::get('location'));
            }

            if (Input::get('type')) {
                $qurbanOrders = $qurbanOrders->whereIn('qurban_type_id', explode(',', Input::get('type')));
            }

            if (Input::get('status')) {

                switch (Input::get('status')) {
                    case 'Approved':
                        $qurbanOrders = $qurbanOrders->whereNotNull('total_donation');
                        break;
                    case 'Expired':
                        $qurbanOrders = $qurbanOrders->whereNull('total_donation')->where('expired_at', '<', now());
                        break;
                    case 'Pending':
                        $qurbanOrders = $qurbanOrders->whereNull('total_donation')->where('expired_at', '>', now());
                        break;
                }

            }

            if (Input::get('photo') != 'All') {

                if (Input::get('photo') == 'Uploaded') {
                    $qurbanOrders = $qurbanOrders->whereNotNull('qurban_attachments');
                } elseif (Input::get('photo') == 'Not Uploaded') {
                    $qurbanOrders = $qurbanOrders->whereNull('qurban_attachments');
                }

            }

            if (Input::get('str')) {

                $qurbanOrders = $qurbanOrders->where(function ($query) {
                    $query
                        ->orWhereRaw('lower(donor_name) like ?', [strtolower('%' . Input::get('str') . '%')])
                        ->orWhereRaw('lower(donor_phone) like ?', [strtolower('%' . Input::get('str') . '%')])
                        ->orWhereRaw('lower(donor_email) like ?', [strtolower('%' . Input::get('str') . '%')])
                        ->orWhereRaw('(donation + unique_value)::text like ?', [strtolower('%' . Input::get('str') . '%')]);
                });

            }

            if (Input::get('order_name')) {
                $qurbanOrders = $qurbanOrders->whereRaw('qurban_name ilike ?', [strtolower('%' . Input::get('order_name') . '%')]);
            }

            if (Input::get('channel')) {

                $qurbanOrders = $qurbanOrders->where(function ($query) {
                    $query->orWhere('channel_source', Input::get('channel'))
                        ->orWhere('channel_medium', Input::get('channel'))
                        ->orWhere('channel_campaign', Input::get('channel'))
                        ->orWhere('channel_term', Input::get('channel'))
                        ->orWhere('channel_content', Input::get('channel'));
                });

            }

            if (Input::get('donor_type')) {

                if (Input::get('donor_type') == 'All') {

                    $qurbanOrders = $qurbanOrders->whereNotNull('donor_type');

                } else {

                    $qurbanOrders = $qurbanOrders->where('donor_type', Input::get('donor_type'));

                }

            }

            if (Input::get('report')) {

                if (Input::get('report') == 'Sent') {
                    $qurbanOrders = $qurbanOrders->whereNotNull('send_report_at');
                } else {
                    $qurbanOrders = $qurbanOrders->whereNull('send_report_at');
                }

            }

            if (Input::get('qurban_status')) {
                switch (Input::get('qurban_status')) {
                    case 'Ready Send':
                        $qurbanOrders = $qurbanOrders->whereNotNull('qurban_attachments')->whereNotNull('video_available_at')->whereNull('send_report_at');
                        break;
                    case 'Not Ready Send':
                        $qurbanOrders = $qurbanOrders->where(function ($query) {
                            $query->orWhereNull('qurban_attachments')->orWhereNull('video_available_at');
                        });
                        break;
                    case 'Sent':
                        $qurbanOrders = $qurbanOrders->whereNotNull('send_report_at');
                        break;
                    case 'Video Uploaded':
                        $qurbanOrders = $qurbanOrders->whereNotNull('video_available_at');
                        break;
                    case 'Image Uploaded':
                        $qurbanOrders = $qurbanOrders->whereNotNull('qurban_attachments');
                        break;
                    case 'Video Not Uploaded':
                        $qurbanOrders = $qurbanOrders->whereNull('video_available_at');
                        break;
                    case 'Image Not Uploaded':
                        $qurbanOrders = $qurbanOrders->whereNull('qurban_attachments');
                        break;
                    case 'All':
                        break;
                    default:
                        $qurbanOrders = $qurbanOrders->where('qurban_status', Input::get('qurban_status'));
                        break;
                }
            }

            $offset = 20;

            if (Input::get('offset')) {
                $offset = Input::get('offset');
            }

            return response([
                'status' => 'success',
                'message' => 'Successfully get list order',
                'data' => $qurbanOrders->paginate($offset)
            ]);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function orderAttachment(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'attachment' => 'required',
                'number' => 'required|integer|between:0,2'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            DB::beginTransaction();

            $qurbanOrder = QurbanOrder::findOrFail($request->get('id'));

            $qurbanAttachments = [];

            if ($qurbanOrder->qurban_attachments) {

                $qurbanAttachments = $qurbanOrder->qurban_attachments;

            }

            $fileName = time() . '-' . str_random(50) . '.' . 'jpg';
            $resultImage = ImageFile::storeImage($request->get('attachment'),$fileName);

            if ($resultImage) {
                throw new Exception('Failed to save image. ' . $resultImage);
            }

            if (isset($qurbanAttachments[$request->get('number')])) {
                ImageFile::deleteImage($qurbanAttachments[$request->get('number')]);
            }

            $qurbanAttachments[$request->get('number')] = $fileName;

            $qurbanOrder->update([
                'qurban_attachments' => $qurbanAttachments,
                'qurban_status' => 'Telah Dipotong'
            ]);

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Successfully add attachment',
                'data' => $qurbanOrder
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function orderAttachmentDelete(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'attachment_name' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            DB::beginTransaction();

            $qurbanOrder = QurbanOrder::findOrFail($request->get('id'));

            if (!$qurbanOrder->qurban_attachments) {

                throw new Exception('Attachment not found.');

            }

            $qurbanAttachments = $qurbanOrder->qurban_attachments;

            $resultImage = ImageFile::deleteImage($request->get('attachment_name'));

            if ($resultImage) {
                throw new Exception('Failed to save image. ' . $resultImage);
            }

            if (($key = array_search($request->get('attachment_name'), $qurbanAttachments)) !== false) {
                unset($qurbanAttachments[$key]);
            } else {
                throw new Exception('Attachment name not found on attachment list.');
            }

            $qurbanOrder->update([
                'qurban_attachments' => $qurbanAttachments
            ]);

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Successfully delete attachment',
                'data' => $qurbanOrder
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function orderStatus(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'qurban_status' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            DB::beginTransaction();

            $qurbanOrder = QurbanOrder::findOrFail($request->get('id'));

            $qurbanOrder->update([
                'qurban_status' => $request->get('qurban_status')
            ]);

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Successfully update order status',
                'data' => $qurbanOrder
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function orderLocationUpdate(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'parent_id' => 'required',
                'location_id' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            DB::beginTransaction();

            $qurbanOrder = QurbanOrder::orWhere('parent_id', $request->get('parent_id'))
                ->orWhere('id', $request->get('parent_id'));

            $qurbanOrder->update([
                'qurban_location_id' => $request->get('location_id')
            ]);

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Successfully update order location',
                'data' => $qurbanOrder->get()
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function orderDetail($donationNumber)
    {

        try {

            $donation = Donation::with(
                'bank:id,bank_account,bank_number,bank_info',
                'qurban_order:id,parent_id,donation_id,qurban_type_id,qurban_location_id,qurban_name,qurban_status'
            )
                ->select('id', 'bank_id', 'donor_name', 'donor_phone', 'donor_email', 'donation_number', 'date_donation', 'expired_at', 'donation', 'unique_value', 'total_donation')
                ->where('donation_number', $donationNumber)->firstOrFail();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get qurban order detail';
            $res['data'] = $donation;
            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }

    }

    public function orderReport($id)
    {
        $qurbanOrder = QurbanOrder::findOrFail($id);
        $attach = array();
        $attach[0] = 'dummy-attach-kurban1.png';
        $attach[1] = 'dummy-attach-kurban2.png';
        $attach[2] = 'dummy-attach-kurban3.png';

        if($qurbanOrder->qurban_attachments){
            foreach($qurbanOrder->qurban_attachments as $key => $attachment){
                $attach[$key] = $attachment;
            }
        }
        $qurbanOrder->attachment = $attach;
        $is_kemitraan = ($qurbanOrder->donation->channel_term) ? '-kemitraan' : '';
        return view('emails.insanbumimandiri.report-qurban'.$is_kemitraan, compact('qurbanOrder'));
    }

    public function orderReportGenerate($id)
    {

        $qurbanOrder = QurbanOrder::findOrFail($id);
        $attach = array();
        $attach[0] = 'dummy-attach-kurban1.png';
        $attach[1] = 'dummy-attach-kurban2.png';
        $attach[2] = 'dummy-attach-kurban3.png';

        if($qurbanOrder->qurban_attachments){
            foreach($qurbanOrder->qurban_attachments as $key => $attachment){
                $attach[$key] = $attachment;
            }
        }
        $qurbanOrder->attachment = $attach;
        return view('emails.insanbumimandiri.report-qurban-generate', compact('qurbanOrder'));
    }

    public function orderReportVideo($fileName)
    {
        try {

            $filePath = storage_path("app/public/qurban-report/videos/$fileName");

            if (!file_exists($filePath)) {
                throw new Exception ("Video Report belum tersedia");
            }

            $fileNameSplitted = explode(".", $fileName);

            $fileNameSplitted = explode("-", $fileNameSplitted[0]);

            $qurbanOrder = QurbanOrder::findOrFail($fileNameSplitted[1]);

            return response()->file($filePath, [
                'Content-Type' => 'video/mp4',
                'Content-Disposition' => 'inline; filename="Report Qurban 2020 - ' . $qurbanOrder->qurban_name .'"'
            ]);

        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function orderReportImage($fileName)
    {
        try {

            $filePath = storage_path("app/public/qurban-report/images/$fileName");

            if (!file_exists($filePath)) {
                throw new Exception ("Image Report belum tersedia");
            }

            return response()->file($filePath, [
                'Content-Type' => 'image/jpg',
                'Content-Disposition' => 'inline; filename="Lesson-file"'
            ]);

        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function orderSendReport(Request $request){

        try{

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'worker' => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $qurbanOrder = QurbanOrder::findOrFail($request->get('id'));

            if (count(collect($qurbanOrder->qurban_attachments)) != 3) {
                throw new Exception ("Attachment tidak ada/belum lengkap.");
            }

            $donation = Donation::findOrFail($qurbanOrder->donation_id);

            if(count($qurbanOrder->qurban_attachments) != 3){
                throw new Exception ("Attachment Report belum lengkap");
            }

            if ($donation->donor_phone) {

                WhatsappJob::firstOrCreate(
                    [
                        'job_type' => 'Report Qurban',
                        'qurban_order_id' => $qurbanOrder->id,
                        'whatsapp_number' => $donation->donor_phone,
                        'worker' => $request->get('worker')
                    ],
                    [
                        'job_status' => 'On Queue',
                        'priority' => 1,
                        'whatsapp_name' => $donation->donor_name,
                        'worker_mode' => 'anon',
                    ]
                );
            }

            if ($donation->donor_email) {
                $memberName = explode( ".", env('APP_MEMBER'))[0];

                $fileName = "emails.$memberName.send-report-qurban";
                $subject = "Laporan Qurban IBM 2020";
                $data['qurbanOrder'] = $qurbanOrder;
                $data['donation'] = $donation;

                Mail::send($fileName, $data, function ($message) use ($donation, $subject) {
                    $message->to($donation->donor_email, $donation->donor_name)
                        ->subject($subject);
                    $message->from(env('MAIL_SENDER'), env('EMAIL_NAME'));
                });

            }

            $qurbanOrder->update(['send_report_at'=>now()]);

            $res['status'] = 'success';
            $res['message'] = 'Successfully send report order';
            $res['data'] = null;
            return response($res, 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function orderPlotting(Request $request){

        try{

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'parent_id' => 'required',
                'location_id' => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $qurbanOrders = QurbanOrder::whereIn('id', $request->get('parent_id'))->orWhereIn('parent_id', $request->get('parent_id'));
            $result = $qurbanOrders->update([
                'qurban_location_id' => $request->get('location_id'),
                'qurban_status' => 'Siap Dipotong'
            ]);

            DB::commit();

            $res['status'] = 'success';
            $res['message'] = 'Successfully update '.$result.' data';
            $res['data'] = $qurbanOrders->get();
            return response($res, 200);
        }catch(Exception $exception){
            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function orderPayment()
    {
        try {

            $donations = Donation::with(
                'bank:id,bank_account,bank_number,bank_info'
            )
                ->leftJoin('qurban_orders', 'donations.id', '=', 'qurban_orders.donation_id')
                ->leftJoin('qurban_types', 'qurban_orders.qurban_type_id', '=', 'qurban_types.id')
                ->groupBy(['donations.id', 'qurban_types.type_name', 'date_donation'])
                ->orderByDesc('date_donation')
                ->whereNotNull('qurban_orders.id')
                ->select([
                    'donations.id',
                    'date_donation',
                    'donation_number',
                    DB::raw('count(qurban_orders.id) filter ( where parent_id is null ) qurban_quantity'),
                    'qurban_types.type_name as qurban_type',
                    'donation',
                    'unique_value',
                    'total_donation',
                    'bank_id',
                    'donor_name',
                    'expired_at'
                ]);

            if (Input::get('start_date') and Input::get('end_date')) {
                $donations = $donations->where('date_donation', '>=', Input::get('start_date'))
                    ->where('date_donation', '<=', Input::get('end_date'));
            }

            if (Input::get('str')) {

                $donations = $donations->where(function ($query) {
                    $query
                        ->orWhereRaw('lower(donor_name) like ?', [strtolower('%' . Input::get('str') . '%')])
                        ->orWhereRaw('lower(donor_phone) like ?', [strtolower('%' . Input::get('str') . '%')])
                        ->orWhereRaw('lower(donor_email) like ?', [strtolower('%' . Input::get('str') . '%')])
                        ->orWhereRaw('(donation + unique_value)::text like ?', [strtolower('%' . Input::get('str') . '%')]);
                });

            }

            if (Input::get('type')) {
                $donations = $donations->whereIn('qurban_type_id', explode(',', Input::get('type')));
            }

            if (Input::get('status')) {

                switch (Input::get('status')) {
                    case 'Approved':
                        $donations = $donations->whereNotNull('total_donation');
                        break;
                    case 'Expired':
                        $donations = $donations->whereNull('total_donation')->where('expired_at', '<', now());
                        break;
                    case 'Pending':
                        $donations = $donations->whereNull('total_donation')->where('expired_at', '>', now());
                        break;
                }

            }

            if (Input::get('bank')) {
                $donations = $donations->whereIn('bank_id', explode(',', Input::get('bank')));
            }

            $offset = 20;

            if (Input::get('offset')) {
                $offset = Input::get('offset');
            }

            $donations = $donations->paginate($offset);

            return response([
                'status' => 'success',
                'messages' => 'Successfully get payment list',
                'data' => $donations
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function orderType()
    {
        try {

            $qurbanOrders = QurbanOrder::select(
                DB::raw('coalesce(parent_id, qurban_orders.id) as parent'),
                'qurban_orders.donor_id',
                'donation_id',
                'qurban_type_id',
                'qurban_location_id',
                DB::raw("string_agg(qurban_name, ', ') qurban_name"),
                DB::raw('round(sum(qurban_price)) qurban_price'),
                'channel_source',
                'channel_medium',
                'channel_campaign',
                'channel_term',
                'channel_content',
                'expired_at',
                'donor_name',
                'date_donation',
                'donation',
                'unique_value',
                'total_donation'
            )
                ->leftJoin('donations', 'qurban_orders.donation_id', '=', 'donations.id')
                ->whereNotNull('donations.total_donation')
                ->groupBy(
                    DB::raw('coalesce(parent_id, qurban_orders.id)'),
                    'qurban_orders.donor_id',
                    'donation_id',
                    'qurban_type_id',
                    'qurban_location_id',
                    'channel_source',
                    'channel_medium',
                    'channel_campaign',
                    'channel_term',
                    'channel_content',
                    'expired_at',
                    'donor_name',
                    'date_donation',
                    'donation',
                    'unique_value',
                    'total_donation'
                );

            if (Input::get('start_date') and Input::get('end_date')) {
                $qurbanOrders = $qurbanOrders->where('date_donation', '>=', Input::get('start_date'))
                    ->where('date_donation', '<=', Input::get('end_date'));
            }

            if (Input::get('location')) {
                $qurbanOrders = $qurbanOrders->where('qurban_location_id', Input::get('location'));
            }

            if (Input::get('type')) {
                $qurbanOrders = $qurbanOrders->whereIn('qurban_type_id', explode(',', Input::get('type')));
            }

            if (Input::get('status')) {

                switch (Input::get('status')) {
                    case 'Approved':
                        $qurbanOrders = $qurbanOrders->whereNotNull('total_donation');
                        break;
                    case 'Expired':
                        $qurbanOrders = $qurbanOrders->whereNull('total_donation')->where('expired_at', '<', now());
                        break;
                    case 'Pending':
                        $qurbanOrders = $qurbanOrders->whereNull('total_donation')->where('expired_at', '>', now());
                        break;
                }

            }

            if (Input::get('photo') != 'All') {

                if (Input::get('photo') == 'Uploaded') {
                    $qurbanOrders = $qurbanOrders->whereNotNull('qurban_attachments');
                } elseif (Input::get('photo') == 'Not Uploaded') {
                    $qurbanOrders = $qurbanOrders->whereNull('qurban_attachments');
                }

            }

            if (Input::get('str')) {

                $qurbanOrders = $qurbanOrders->where(function ($query) {
                    $query
                        ->orWhereRaw('lower(donor_name) like ?', [strtolower('%' . Input::get('str') . '%')])
                        ->orWhereRaw('lower(donor_phone) like ?', [strtolower('%' . Input::get('str') . '%')])
                        ->orWhereRaw('lower(donor_email) like ?', [strtolower('%' . Input::get('str') . '%')])
                        ->orWhereRaw('(donation + unique_value)::text like ?', [strtolower('%' . Input::get('str') . '%')]);
                });

            }

            if (Input::get('order_name')) {
                $qurbanOrders = $qurbanOrders->whereRaw('qurban_name ilike ?', [strtolower('%' . Input::get('order_name') . '%')]);
            }

            if (Input::get('channel')) {

                $qurbanOrders = $qurbanOrders->where(function ($query) {
                    $query->orWhere('channel_source', Input::get('channel'))
                        ->orWhere('channel_medium', Input::get('channel'))
                        ->orWhere('channel_campaign', Input::get('channel'))
                        ->orWhere('channel_term', Input::get('channel'))
                        ->orWhere('channel_content', Input::get('channel'));
                });

            }

            if (Input::get('donor_type')) {

                if (Input::get('donor_type') == 'All') {

                    $qurbanOrders = $qurbanOrders->whereNotNull('donor_type');

                } else {

                    $qurbanOrders = $qurbanOrders->where('donor_type', Input::get('donor_type'));

                }

            }

            if (Input::get('report')) {

                if (Input::get('report') == 'Sent') {
                    $qurbanOrders = $qurbanOrders->whereNotNull('send_report_at');
                } else {
                    $qurbanOrders = $qurbanOrders->whereNull('send_report_at');
                }

            }

            if (Input::get('qurban_status')) {
                $qurbanOrders = $qurbanOrders->where('qurban_status', Input::get('qurban_status'));
            }

            $offset = 20;

            if (Input::get('offset')) {
                $offset = Input::get('offset');
            }

            return response([
                'status' => 'success',
                'messages' => 'Successfully get payment list',
                'data' => $qurbanOrders->paginate($offset)
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function orderUpdate(Request $request){

        try{

            DB::beginTransaction();

            $qurbanOrder = QurbanOrder::findOrFail($request->get('order_id'));

            $qurbanOrderData = [];
            $donationData = [];

            if ($request->get('donor_name')) {
                $donationData['donor_name'] = $request->get('donor_name');
            }

            if ($request->get('donor_phone')) {
                $donationData['donor_phone'] = $request->get('donor_phone');
            }

            if ($request->get('donor_email')) {
                $donationData['donor_email'] = $request->get('donor_email');
            }

            if ($request->get('qurban_name')) {
                $qurbanOrderData['qurban_name'] = $request->get('qurban_name');
            }

            if ($request->get('type_id')) {
                $qurbanOrderData['qurban_type_id'] = $request->get('type_id');
            }

            if ($request->get('location_id')) {
                $qurbanOrderData['qurban_location_id'] = $request->get('location_id');
            }

            if (count($qurbanOrderData) > 0) {
                $qurbanOrder->update($qurbanOrderData);
            }

            if (count($donationData) > 0) {
                Donation::findOrFail($qurbanOrder->donation_id)->update($donationData);
            }

            DB::commit();

            return response([
                'status' => 'success',
                'messages' => 'Successfully update order',
                'data' => $qurbanOrder
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function orderCheck(Request $request)
    {
        try {

            DB::beginTransaction();

            $qurbanOrder = QurbanOrder::findOrFail($request->get('id'));
            $qurbanOrder->update([
                'check_at' => now(),
                'check_id' => $request->get('staff_id')
            ]);

            DB::commit();

            return response([
                'status' => 'success',
                'messages' => 'Successfully check qurban order',
                'data' => $qurbanOrder
            ], 200);

        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'messages' => $exception->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function locationReport($id)
    {
        $qurbanLocation = QurbanLocation::findOrFail($id);
        return view('emails.insanbumimandiri.location-report', compact('qurbanLocation'));

    }

    public function qurbanReceipt($donation_number)
    {
        // return view('qurban')
    }
}
