<?php

namespace App\Http\Controllers;

use App\Models\ImageFile;
use App\Models\Volunteer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class VolunteerController extends Controller
{
    public function index()
    {
        $offset = 15;

        if (Input::get('offset')) {

            $offset = Input::get('offset');

        }

        $res['status'] = 'success';
        $res['message'] = 'Successfully get all volunteer';
        $res['data'] = Volunteer::ofMember(env('APP_MEMBER'))->latest()->paginate($offset);

        return response($res, 200);

    }

    public function store(Request $request)
    {

        try {

            DB::beginTransaction();

            $imageBase64 = $request->input('photo');
            $fileName = ImageFile::storeImage($imageBase64);

            if (!$fileName) {
                throw new Exception('Failed save photo', 500);
            }

            $request->request->add([
                'photo' => $fileName,
                'volunteer_status' => 'Waiting Approved'
            ]);

            $volunteer = Volunteer::create($request->all());

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create user';
            $res['data'] = $volunteer;
            return response($res);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, $exception->getCode());

        }

    }

}
