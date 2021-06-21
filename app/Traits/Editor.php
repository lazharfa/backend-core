<?php


namespace App\Traits;


use DOMDocument;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait Editor
{
    protected function base64ToFile($htmlRaw, $prefixName)
    {
        try {
            $domain = explode('.', env('APP_MEMBER'))[0];

            $doc = new DOMDocument();

            $doc->loadHTML($htmlRaw);

            $tags = $doc->getElementsByTagName('img');

            foreach ($tags as $key => $tag) {

                $oldSrc = $tag->getAttribute('src');

                if (!filter_var($oldSrc, FILTER_VALIDATE_URL)) {
                    $fileName = "{$prefixName}-campaign-body-$key.jpg";

                    list(, $img) = explode(',', $oldSrc);

                    $img = base64_decode($img);

                    $filePath = "$domain/public/$fileName";

                    if (!Storage::disk('do_spaces')->put($filePath, $img)) {
                        throw new Exception("Failed upload to Space.");
                    }

                    $region = env('DO_SPACES_REGION');
                    $bucket = env('DO_SPACES_BUCKET');

                    $tag->setAttribute('src', "https://$bucket.$region.cdn.digitaloceanspaces.com/$filePath");
                }

            }

            $htmlString = $doc->saveHTML();

            $htmlString = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', '', $htmlString);
            $htmlString = str_replace('<html><body>', '', $htmlString);
            $htmlString = str_replace('</body></html>', '', $htmlString);

            return [$htmlString, null];

        } catch (Exception $exception) {
            return [null, $exception->getMessage()];
        }

    }
}
