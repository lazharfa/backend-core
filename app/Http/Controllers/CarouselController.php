<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use App\Models\ImageFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class CarouselController extends Controller
{
    public function index()
    {
        try {

            $res['status'] = 'success';
            $res['message'] = 'Successfully get carousels';
            $res['data'] = Carousel::ofMember(env('APP_MEMBER'))->latest();

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

            $imageBase64 = $request->input('image_content');
            $fileName = ImageFile::storeImage($imageBase64);

            if (!$fileName) {
                throw new Exception('Failed save image');
            }

            $request->request->add([
                'creator_id' => Auth::id(),
                'file_name' => $fileName
            ]);

            $carousel = Carousel::create($request->all());

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create carousel';
            $res['data'] = $carousel;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = $request->all();
            return response($res, 200);

        }
    }

    public function show($slug)
    {

        try {

            $campaignNews = Carousel::ofMember(env('APP_MEMBER'))->where('carousel_slug', $slug)->firstOrFail();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get carousel';
            $res['data'] = $campaignNews;
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

            $carousel = Carousel::ofMember(env('APP_MEMBER'))->where('file_name', $slug)->firstOrFail();
            $imageBase64 = $request->input('image_content');
            $fileName = ImageFile::storeImage($imageBase64);

            if (!$fileName) {
                throw new Exception('Failed save image');
            }

            $request->request->add([
                'updater_id' => Auth::id(),
                'file_name' => $fileName
            ]);

            $carousel->update($request->all());

            $res['status'] = 'success';
            $res['message'] = 'Successfully update carousel';
            $res['data'] = $request->all();
            DB::commit();

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function destroy($slug)
    {

        try {

            DB::beginTransaction();
            Carousel::ofMember(env('APP_MEMBER'))->where('file_name', $slug)->firstOrFail()->delete();

            $res['status'] = 'success';
            $res['message'] = 'Successfully delete carousel';
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
