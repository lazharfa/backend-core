<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageFile extends Model
{
    protected $fillable = [
        'file_name', 'file_content'
    ];

    public static function storeImage($fileContent, &$fileName)
    {
        try {

            if (!$fileName) {
                $fileName = time() . '-' . Str::random(50) . '.' . 'jpg';
            }

            ImageFile::create([
                'file_name' => $fileName,
                'file_content' => $fileContent
            ]);

            return null;

        } catch (Exception $exception) {

            return $exception->getMessage();
        }
    }

    public static function storeImageWithCrop($fileName, $fileContent, $angle, $scale, $height, $width, $x, $y)
    {

        try {

            $filePath = storage_path("app/public/uploads/$fileName");
            list(, $img) = explode(',', $fileContent);
            $img = base64_decode($img);

            // save image to directory
            File::put($filePath, $img);
            $image = Image::make($filePath)->rotate((float)$angle);

            //we get the image width then multiply it by the scale factor, it will also scale the height automatically
            $image->widen((int)($image->width() * $scale));

            // crop image
            $image->crop((int)$width, (int)$height, (int)$x, (int)$y);
            $image->save($filePath);

            if (!ImageFile::uploadToSpace($fileName)) {
                throw new Exception("Failed upload to Space.");
            }

            return null;

        } catch (Exception $exception) {

            return $exception->getMessage();

        }

    }

    public static function uploadToSpace($fileName)
    {
        $domain = explode('.', env('APP_MEMBER'))[0];

        $localPath = storage_path("app/public/uploads/$fileName");

        if (!File::exists($localPath)) {
            return false;
        }

        $fileContent  = File::get(storage_path("app/public/uploads/$fileName"));

        $filePath = "$domain/public/$fileName";

        return Storage::disk('do_spaces')->put($filePath, $fileContent);
    }

    public static function deleteImage($fileName)
    {
        try {

            $imageFile = ImageFile::where('file_name', $fileName)->first();

            if ($imageFile) {
                $imageFile->delete();
            }

            return null;

        } catch (Exception $exception) {

            return $exception->getMessage();
        }
    }
}
