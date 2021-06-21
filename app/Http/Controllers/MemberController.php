<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class MemberController extends Controller
{
    public function index()
    {
        try {

            $offset = 15;

            if (Input::get('offset')) {
                $offset = Input::get('offset');
            }

            $members = Member::where(function ($query) {
                $query->where('id', 'like', '%' . Input::get('str') . '%')
                    ->orWhere('member_id', 'like', '%' . Input::get('str') . '%');
            })->latest()->paginate($offset);

            $res['status'] = 'success';
            $res['message'] = 'Successfully get members';
            $res['data'] = $members;
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
            $member = Member::create($request->all());
            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create member';
            $res['data'] = $member;
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

            $member = Member::findOrFail($id);
            $res['status'] = 'success';
            $res['message'] = 'Successfully get member';
            $res['data'] = $member;
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

            Member::findOrFail($id)->update($request->all());
            $res['status'] = 'success';
            $res['message'] = 'Successfully update member';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function amount_donation(Request $request)
    {

    }
}
