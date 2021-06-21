<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\BalansDisbursement;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Exception;

class BalansController extends Controller
{
    public function importTransaction(Request $request)
    {
    	try {

            $validator = Validator::make($request->all(), [
                'file' 		=> ['required','file'],
                'source'	=> ['required', 'in:midtrans,faspay,ovo'],
                'date'		=> ['required']
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            Excel::import(new BalansDisbursement($request->input('date'), $request->source), $request->file('file'));

            $res['status'] = 'success';
            $res['message'] = 'Successfully import';
            $res['data'] = null;
            return response($res, 200);
        } catch (Exception $exception) {

            $message = $exception->getMessage();

            if (json_decode($message)) {
                $message = json_decode($message);
            }

            return response([
                'success' => false,
                'message' => $message
            ], 500);

        }
    }
}
