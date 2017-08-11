<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;

class ImageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getGuestImage(Request $request, $filename)
    {
        $img = app('image');

        return response($img->content, 200)
            ->header('Content-Type', $img->mimetype)
            ->header('Content-Length', $img->filesize)
            ->header('Cache-Control', 'max-age=604800,public');
    }

    public function getGuestThumb(Request $request, $filename)
    {

    }

    public function getUserImage(Request $request, $filename)
    {

    }

    public function getUserThumb(Request $request, $filename)
    {

    }

    public function getHash(Request $request, $hash)
    {
        $hash_path = split_to_path($hash);
        if (Storage::disk('s3')->has($hash_path))
        {
            return response(Storage::disk('s3')->get($hash_path))
                ->header('Content-Type', 'application/octet-stream');
        }
    }
}
