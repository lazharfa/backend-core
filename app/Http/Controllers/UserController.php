<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;

class UserController extends Controller
{

    public function index(Request $request)
    {
        try {

            $users = User::ofMember(env('APP_MEMBER'));

            if ($request->get('str')) {

                $users = $users->where(function ($query) use ($request) {
                    $query->where('full_name', 'like', '%' . $request->get('str') . '%')
                        ->orWhere('email', 'like', '%' . $request->get('str') . '%')
                        ->orWhere('phone_number', 'like', '%' . $request->get('str') . '%');
                });

            };

            $offset = 15;
            if ($request->get('offset')) {
                $offset = $request->get('offset');
            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully get users';
            $res['data'] = $users->paginate($offset);

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
            $user = User::create($request->all());
            $role = Role::where('slug', $request->input('role'))->first();
            $user->attachRole($role);
            $user->roles;
            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create user';
            $res['data'] = $user;
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

            DB::beginTransaction();
            $user = User::with('donations')->ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail();
            $res['status'] = 'success';
            $res['message'] = 'Successfully get user';
            $res['data'] = $user;
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function showProfile()
    {
        $user = Auth::user();
        $donor_type = $user->donorType ? $user->donorType->type : null;
        $response = [
            'id'            => $user->id,
            'full_name'          => $user->full_name,
            'member_id'          => $user->member_id,
            'member_bank_id'      => $user->member_bank_id,
            'email'             => $user->email,
            'phone_number'          => $user->phone_number,
            'donor_type'          => $donor_type,
        ];

        return response([
            'status'    => 'success',
            'message'   => null,
            'data'      => $response
        ], 200);
    }

    public function update(Request $request, $id)
    {

        try {

            DB::beginTransaction();
            $user = User::where('id', $id)->firstOrFail()->update($request->all());
            $res['status'] = 'success';
            $res['message'] = 'Successfully update user';
            $res['data'] = $user;
            DB::commit();

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function updateProfile(Request $request)
    {
        return $this->update($request, Auth::id());
    }

}
