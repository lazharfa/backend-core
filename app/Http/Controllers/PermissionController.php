<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class PermissionController extends Controller
{
    public function attach(Request $request)
    {

        try {

            DB::beginTransaction();

            foreach ($request->input('roles') as $roleId) {

                Role::findOrFail($roleId)->attachPermission($request->input('permissions'));

            }

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully attach permissions';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function attachAll(Request $request)
    {

        try {

            DB::beginTransaction();

            foreach ($request->input('roles') as $roleId) {

                $permissions = Permission::all()->map(function ($permission) {
                    return $permission->id;
                })->toArray();

                Role::findOrFail($roleId)->attachPermission($permissions);

            }

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully attach permissions';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function detach(Request $request)
    {

        try {

            DB::beginTransaction();

            foreach ($request->input('roles') as $roleId) {

                Role::findOrFail($roleId)->detachPermission($request->input('permissions'));

            }

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully detach permissions';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function detachAll(Request $request)
    {

        try {

            DB::beginTransaction();

            foreach ($request->input('roles') as $roleId) {

                Role::findOrFail($roleId)->detachAllPermission();

            }

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully detach permissions';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function sync(Request $request)
    {

        try {

            DB::beginTransaction();

            foreach ($request->input('roles') as $roleId) {

                Role::findOrFail($roleId)->syncPermission($request->input('permissions'));

            }

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully detach permissions';
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
