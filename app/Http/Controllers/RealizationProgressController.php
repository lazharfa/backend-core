<?php

namespace App\Http\Controllers;

use App\Models\BenefitRecipient;
use App\Models\RealizationProgress;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Console\Input\Input;


class RealizationProgressController extends Controller
{
    public function store(Request $request)
    {

        try {

            DB::beginTransaction();
            $input = $request->all();
            $input['creator_id'] = Auth::id();
            $realizationProgress = RealizationProgress::create($input);

            if ($request->hasFile('file')) {
                $extension = File::extension($request->file->getClientOriginalName());

                if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {

                    $path = $request->file('file')->getRealPath();
                    $data = Excel::load($path, function ($reader) {
                    })->get();


                    if (!empty($data) && $data->count()) {


                        foreach ($data as $key => $value) {

                            BenefitRecipient::create([
                                'campaign_id' => $input['campaign_id'],
                                'name' => $value['name'],
                                'address' => $value['address'],
                                'birthday' => $value['birthday'],
                                'gender' => $value['gender'],
                            ]);

                        }

                    }

                    throw new Exception('Data is null.');

                }

                throw new Exception('Extension not support.');
            }

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create progress campaign';
            $res['data'] = $realizationProgress;
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
            $input = $request->all();
            $input['updater_id'] = Auth::id();
            RealizationProgress::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail()->update($input);

            $res['status'] = 'success';
            $res['message'] = 'Successfully update progress campaign';
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

    public function destroy($id)
    {

        try {

            DB::beginTransaction();
            RealizationProgress::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail()->delete();

            $res['status'] = 'success';
            $res['message'] = 'Successfully delete progress campaign';
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
