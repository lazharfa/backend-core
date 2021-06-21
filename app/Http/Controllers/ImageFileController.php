<?php

namespace App\Http\Controllers;

use App\Models\ImageFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

class ImageFileController extends Controller
{
    public function index()
    {
        $imageFiles = ImageFile::latest()->get()->map(function ($imageFile) {
            return url('api/image/' . $imageFile->file_name);
        });

        return $imageFiles;
    }

    public function store(Request $request)
    {

        try {

            DB::beginTransaction();

            $fileName = time() . '-' . str_random(50) . '.' . 'jpg';
            $resultImage = ImageFile::storeImageWithCrop($fileName, $request->get('image'), $request->get('angle'), $request->get('scale'), $request->get('h'), $request->get('w'), $request->get('x'), $request->get('y'));

            if ($resultImage) {

                throw new Exception('Failed to save image. ' . $resultImage);

            }

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create image';
            $res['data'] = $fileName;

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function show($name)
    {

        try {

            $filePath = storage_path("app/public/uploads/$name");

            if (file_exists(storage_path("app/public/qurban-report/source/$name"))) {
                $filePath = storage_path("app/public/qurban-report/source/$name");
            } elseif (!file_exists($filePath)) {

                $imageFile = ImageFile::where('file_name', $name)->first();

                $imageFile->file_content;

                $base64 = null;

                try {

                    list(, $base64) = explode(',', $imageFile->file_content);

                } catch (Exception $exception) {

                    $base64 = $imageFile->file_content;

                }

                if (!$imageFile) {

                    throw new Exception('Image Not Found');

                }

                File::put($filePath, base64_decode($base64));

            }

            $cacheImage = Image::cache(function ($image) use ($filePath) {

                return $image->make($filePath);

            }, 120);

            return response($cacheImage, 200, array('Content-Type' => 'image/jpg'));

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function showByWidth($width, $name)
    {

        try {

            $filePath = storage_path("app/public/uploads/$width-$name");

            if (!file_exists($filePath)) {

                if (file_exists(storage_path("app/public/uploads/$name"))) {

                    Storage::copy("public/uploads/$name", "public/uploads/$width-$name");

                } else {

                    $imageFile = ImageFile::where('file_name', $name)->first();

                    $imageFile->file_content;

                    $base64 = null;

                    try {

                        list(, $base64) = explode(',', $imageFile->file_content);

                    } catch (Exception $exception) {

                        $base64 = $imageFile->file_content;

                    }

                    if (!$imageFile) {

                        throw new Exception('Image Not Found');

                    }

                    File::put($filePath, base64_decode($base64));

                }

            }

            $cacheImage = Image::cache(function ($image) use ($filePath, $width) {

                return $image->make($filePath)->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

            }, 120);

            return response($cacheImage, 200, array('Content-Type' => 'image/jpg'));

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|file|image|max:3000',
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $domain = explode('.', env('APP_MEMBER'))[0];
            $img = $request->file('image');
            $fileName = str_random(2) . str_replace(' ', '-', $img->getClientOriginalName());

            $filePath = "$domain/host/$fileName";

            if (!Storage::disk('do_spaces')->put($filePath, file_get_contents($img))) {
                throw new Exception("Failed upload to Space.");
            }

            $region = env('DO_SPACES_REGION');
            $bucket = env('DO_SPACES_BUCKET');

            $res['status'] = 'success';
            $res['message'] = 'Successfully create image';
            $res['data'] = "https://$bucket.$region.cdn.digitaloceanspaces.com/$filePath";

            return response($res);
        } catch (Exception $e) {
            $res['status'] = 'error';
            $res['message'] = $e->getMessage();
            $res['data'] = '';
            return response($res, 400);
        }
    }

}
