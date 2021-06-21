<?php

namespace App\Http\Controllers;

use App\Models\MemberBank;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class MemberBankController extends Controller
{
    public function index()
    {
        try {
            $memberBanks = collect(MemberBank::ofMember(env('APP_MEMBER'))->orderBy('sort')->get());
            $filtered = $memberBanks->filter(function ($value, $key) {
                $faspayMaintenance = [strtotime('2021-05-19 15:00:00'), strtotime('2021-05-19 15:30:00')];
                $now = strtotime(date('Y-m-d H:i:s'));
                
                return (($now > $faspayMaintenance[0] && $now < $faspayMaintenance[1]) && ($value->bank_id == 143)) ? false : true;
            });

            $filtered = $filtered->all();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get member banks';
            $res['data'] = $filtered;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->request->add([
                'member_id' => env('APP_MEMBER')
            ]);

            MemberBank::create($request->all());
            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create member bank';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function show($id)
    {
        try {

            $memberBank = MemberBank::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail();
            $res['status'] = 'success';
            $res['message'] = 'Successfully get member bank';
            $res['data'] = $memberBank;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function update(Request $request, $id)
    {
        try {

            $memberBank = MemberBank::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail()->update($request->all());
            $res['status'] = 'success';
            $res['message'] = 'Successfully update member bank';
            $res['data'] = $memberBank;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function destroy($id)
    {
        try {

            MemberBank::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail()->delete();
            $res['status'] = 'success';
            $res['message'] = 'Successfully delete member bank';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }
}
