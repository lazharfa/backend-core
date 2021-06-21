<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    public function index()
    {
        try {

            $res['status'] = 'success';
            $res['message'] = 'Successfully get banks';
            $res['data'] = Bank::paginate();
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

            $bank = Bank::create($request->all());

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create bank';
            $res['data'] = $bank;
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

            $bank = Bank::findOrFail($id);

            $res['status'] = 'success';
            $res['message'] = 'Successfully get bank';
            $res['data'] = $bank;
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

            $bank = Bank::findOrFail($id)->update($request->all());

            $res['status'] = 'success';
            $res['message'] = 'Successfully update bank';
            $res['data'] = $bank;
            DB::commit();

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = $request->all();
            return response($res, 200);

        }

    }

    public function destroy($id)
    {

        try {

            DB::beginTransaction();
            Bank::findOrFail($id)->firstOrFail()->delete();

            $res['status'] = 'success';
            $res['message'] = 'Successfully delete bank';
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
