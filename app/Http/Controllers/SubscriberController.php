<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    public function index()
    {
        try {

            $subscribers = Subscriber::ofMember(env('APP_MEMBER'));

            $offset = 20;

            if (Input::get('offset')) {
                $offset = Input::get('offset');
            }

            $subscribers = $subscribers->paginate($offset);

            $res['status'] = 'success';
            $res['message'] = 'Successfully get categories';
            $res['data'] = $subscribers;
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

            $validator = Validator::make($request->all(), [
                'member_id' => 'required',
                'email' => 'required|email|max:255'
            ]);


            if ($validator->fails()) {
                $rawString = implode(", ",$validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            DB::beginTransaction();
            $subscriber = Subscriber::create($request->all());
            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create Subscriber';
            $res['data'] = $subscriber;
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
            DB::beginTransaction();
            $subscriber = Subscriber::findOrFail($id)->update($request->all());
            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully update Subscriber';
            $res['data'] = $subscriber;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

}
