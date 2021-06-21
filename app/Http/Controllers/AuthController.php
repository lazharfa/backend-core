<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelRoles\Models\Role;
use Kreait\Firebase\Factory;
use App\Jobs\LoginNotify;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public static function createUserAuth($uid)
    {

        $keyPath = __DIR__ . '/../../../libs/' . env('APP_MEMBER') . '.json';

        $auth = (new Factory)->withServiceAccount($keyPath)->createAuth();

        $user = $auth->getUser($uid);

        if ($userAuth = User::where('uid', $uid)->first()) {

            if ($user->displayName) {

                $dataUser['full_name'] = $user->displayName;

            }
            if ($user->email && !$userAuth->email) {

                $dataUser['email'] = $user->email;

            }

            if ($user->phoneNumber && !$userAuth->phone_number) {

                $dataUser['phone_number'] = str_replace("+", '', $user->phoneNumber);

            }

            $userAuth->update($dataUser);

        } elseif ($user->email) {

            $userAuth = User::where('email', $user->email)->first();

        } elseif ($user->phoneNumber) {

            $userAuth = User::where('phone_number', str_replace("+", '', $user->phoneNumber))->first();

        }

        if (!$userAuth) {

            $dataUser = [
                'member_id' => env('APP_MEMBER'),
                'uid' => $user->uid,
                'full_name' => $user->displayName
            ];

            if ($user->email) {

                $dataUser['email'] = $user->email;

            }

            if ($user->phoneNumber) {

                $dataUser['phone_number'] = str_replace("+", '', $user->phoneNumber);

            }

            $userAuth = User::create($dataUser);

        } elseif (!$userAuth->uid) {

            $userAuth->update([
                'uid' => $user->uid
            ]);

        }

        DB::table('oauth_access_tokens')->where('user_id', $userAuth->id)->update([
            'revoked' => true
        ]);

        return $userAuth;
    }

    public function authDonor(Request $request)
    {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'uid' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ",$validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $userAuth = self::createUserAuth($request->get('uid'));

            $userAuth->token = $userAuth->createToken('SystemInformationIBM')->accessToken;

            if (!$userAuth->hasRole('donor')) {

                $donorRole = Role::find(2); //id 2 is role for donor
                $userAuth->attachRole($donorRole);
                $userAuth->roles;

            }

            $res['status'] = 'success';
            $res['message'] = 'Login user successfully';
            $res['data'] = $userAuth;
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function authStaff(Request $request)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'role' => 'required',
                'uid' => 'required',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ",$validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $userAuth = self::createUserAuth($request->get('uid'));

            $domainUser = explode("@", $userAuth['email'])[1];

            if ($domainUser != env('DOMAIN_ADMIN')) {

                throw new Exception('Your email has been rejected');

            }

            $userAuth->token = $userAuth->createToken('SystemInformationIBM')->accessToken;

            if (!$userAuth->hasRole($request->get('role'))) {

                $donorRole = Role::where('slug', $request->get('role'))->firstOrFail();
                $userAuth->attachRole($donorRole);
                $userAuth->roles;
            }

            DB::commit();

            if ($userAuth->user_status != 'admin') {
                throw new Exception('Anda tidak mempunyai akses ke sistem');
            }

            UserLog::create([
                'user_id'   => $userAuth->id,
                'email'     => $userAuth->email,
                'request'   => json_encode($request->all()),
                'activity'  => 'login',
            ]);

            LoginNotify::dispatchNow($userAuth);

            $res['status'] = 'success';
            $res['message'] = 'Login user successfully';
            $res['data'] = $userAuth;

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function userBankSelected(Request $request)
    {
        $user = Auth::user();

        try {
            $validator = Validator::make($request->all(), [
                'member_bank_id' => 'required'
            ]);
            if ($validator->fails()) {
                $rawString = implode(", ",$validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $user->update($request->only(['member_bank_id']));

            return response([
                'status'    => 'success',
                'message'   => 'Bank updated',
                'data'      => null
            ], 200);
        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function googleLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'accessToken' => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ",$validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $responses = Socialite::driver('google')->userFromToken($request->accessToken);

            $user = User::where('email', $responses->email)->first();
            if (!$user)
            {
                $userCreate = User::create([
                    'email' => $responses->email,
                    'member_id' => env('APP_MEMBER'),
                    'uid' => $responses->id,
                    'full_name' => $responses->name
                ]);

                $user = User::find($userCreate->id);
            }

            if (strpos($user->email, '@adaide.co.id') === false) {
                if (($user->user_status != 'admin') || (strpos($user->email, env('APP_MEMBER')) === false)) {
                    throw new Exception('Anda tidak mempunyai akses ke sistem');
                }
            }

            DB::table('oauth_access_tokens')->where('user_id', $user->id)->update([
                'revoked' => true
            ]);

            $token = $user->createToken('SystemInformationIBM')->accessToken;

            return response([
                'status'    => 'success',
                'message'   => null,
                'data'      => $token
            ], 200);
        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 400);

        }
    }

    public function authUser()
    {
        $user = Auth::user();
        return response([
            'status'    => 'success',
            'message'   => null,
            'data'      => $user
        ], 200);

    }
}
