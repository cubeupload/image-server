<?php

namespace App\Http\Middleware;

use DB;
use Closure;
use App\Models\Image;
use Storage;

class ImageExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get image hash from database/cache.
        // Check if it exists on S3 then request or 404.

        $params = $request->route()[2];

        if (!array_key_exists('filename', $params))
        {
            return response('File not supplied.', 400);
        }

        $filename = $params['filename'];

        $img = Image::whereFilename($filename)->first();

        if (is_null($img))
        {
            return response('Not in database.', 404);
        }

        $hash_path = split_to_path($img->filehash);

        if (!Storage::disk('s3')->has($hash_path))
        {
            return response('Not in storage.', 404);
        }

        $img->content = Storage::disk('s3')->get($hash_path);

        app()->instance('image', $img);

        return $next($request);
    }
}
