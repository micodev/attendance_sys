<?php

namespace App\Traits;

use File;
use Illuminate\Support\Str;

trait UploadImageTrait
{
    public function upload_multi_Pictures($images, $path)
    {

        if (!file_exists(public_path() . $path)) {
            File::makeDirectory(public_path() . $path);
        }
        $names = [];
        foreach ($images as $image) {

            $image = explode(',', $image)[1];
            $imgdata = base64_decode($image);
            $f = finfo_open();
            $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
            $type = explode('/', $mime_type)[1];
            $filename = time() . Str::random(2) . '.' . $type;
            File::put(public_path() . $path . $filename, $imgdata);
            array_push($names, '/images/users/' . $filename);
        }
    }

    public function uploadPicture($picture, $path)
    {

        if (!file_exists(public_path() . $path)) {
            File::makeDirectory(public_path() . $path, 0755, true);
        }
        $picture = explode(',', $picture)[1];
        $imgdata = base64_decode($picture);
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
        $type = explode('/', $mime_type)[1];
        $filename = time() . Str::random(10) . '.' . $type;
        File::put(public_path() . $path . $filename, $imgdata);

        return $path . $filename;
    }
}
