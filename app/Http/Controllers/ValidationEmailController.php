<?php

namespace App\Http\Controllers;

use App\Models\ValidationEmail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ValidationEmailController extends Controller
{
    public function start()
    {
        try {

            DB::beginTransaction();

            $validationEmail = ValidationEmail::where('status', 'On Queue')->first();

            if ($validationEmail) {
                $validationEmail->update([
                    'start_at' => now(),
                    'status' => 'On Worker'
                ]);
            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Successfully get email',
                'data' => $validationEmail
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function done(Request $request)
    {
        try {

            DB::beginTransaction();

            Log::info(json_encode($request->all()));

            $validationEmail = ValidationEmail::findOrFail($request->get('id'));

            if ($validationEmail) {

                $validationEmail->update([
                    'done_at' => now(),
                    'status' => $request->get('status')
                ]);

            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully update email';
            $res['data'] = $validationEmail;

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Successfully update email',
                'data' => $validationEmail
            ], 200);

        } catch (Exception $exception) {

            Log::error($exception->getMessage());
            return response([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'data' => null
            ], 200);

        }
    }

    public function storeFile(Request $request)
    {

        try {

            DB::beginTransaction();

            $file = $request->file('file_source');

            // File Details
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();

            // Valid File Extensions
            $valid_extension = array("csv");

            // 2MB in Bytes
            $maxFileSize = 2097152;

            // Check file extension
            if (!in_array(strtolower($extension), $valid_extension)) {

                throw new Exception('Invalid File Extension.');

            }

            if ($fileSize > $maxFileSize) {

                throw new Exception('File too large. File must be less than 2MB.');

            }

            $file = fopen($tempPath, "r");

            $importData_arr = array();
            $i = 0;

            while (($fileData = fgetcsv($file, 1000, ",")) !== FALSE) {

                $num = count($fileData);

                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = $fileData [$c];
                }

                $i++;

            }

            fclose($file);

            foreach ($importData_arr as $data) {

                ValidationEmail::create([
                    'name' => $data[0],
                    'email' => $data[1]
                ]);

            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Successfully create whatsapp job.',
                'data' => ''
            ], 200);

        } catch (Exception $exception) {

            return response([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'data' => ''
            ], 200);

        }

    }
}
