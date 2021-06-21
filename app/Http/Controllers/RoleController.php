<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        try {

            $offset = 15;

            if (Input::get('offset')) {
                $offset = Input::get('offset');
            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully get roles';
            $res['data'] = Role::paginate($offset);

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

            $role = Role::create([
                'name' => $request->input('name'),
                'slug' => $request->input('slug'),
                'description' => $request->input('description'),
                'level' => $request->input('level'),
            ]);

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create role';
            $res['data'] = $role;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function show($slug)
    {

        try {

            $role = Role::where('slug', $slug)->firstOrFail();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get role';
            $res['data'] = $role;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function update(Request $request, $slug)
    {

        try {

            DB::beginTransaction();

            $role = Role::where('slug', $slug)->firstOrFail();

            $role->update([
                'name' => $request->input('name'),
                'slug' => $request->input('slug'),
                'description' => $request->input('description'),
                'level' => $request->input('level'),
            ]);

            $res['status'] = 'success';
            $res['message'] = 'Successfully update role';
            $res['data'] = $role;
            DB::commit();

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = $request->all();
            return response($res, 200);

        }

    }

    public function destroy($slug)
    {

        try {

            DB::beginTransaction();
            Role::where('slug', $slug)->firstOrFail()->delete();

            $res['status'] = 'success';
            $res['message'] = 'Successfully delete role';
            $res['data'] = '';
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }
}
