<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//$pid = getmypid();
//Redis::hmset('pid:' . $pid, 'lastreq', Request::path(), 'time', time(), 'client', Request::server('REMOTE_ADDR'), 'ref', Request::server('HTTP_REFERER'));

use Illuminate\Http\Request;

$app->get('/', function () use ($app) {
	return redirect('http://cubeupload.com');
});

$app->get('/{user}/t/{file}', function( $user, $file )
{
	$filename = $user . '/' . $file;
	$path = env("USER_IMAGES_DIR") . '/' . $filename;
	$thumb_exp = env("CACHE_THUMB_EXPIRE");
	$thumb = null;

	if( file_exists( $path ) )
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $path);
		finfo_close($finfo);

		if( Cache::has( $filename . ':thumb' ) )
		{
			$thumb = Cache::get( $filename . ':thumb' );
		}
		else
		{
			$thumb = generateThumb( $path );
			Cache::put( $filename . ':thumb', $thumb, $thumb_exp );
		}

		return response($thumb)->header('Content-Type', $mime);
	}
	else
	{
		Cache::forget( $filename .':thumb' );
		return response('Not found.', 404);
	}	
});

$app->get('/t/{file}', function( $filename ) use ($app)
{
	$file = split_filename( $filename );
	$path = env("GUEST_IMAGES_DIR") . '/' . $file;
	$thumb_exp = env("CACHE_THUMB_EXPIRE");
	$thumb = null;
	$mime = null;

	$sTime = microtime(true);

	if( file_exists( $path ) ) // load from original store
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $path);
		finfo_close($finfo);

		if( Cache::has( $filename . ':thumb' ) )
			$thumb = Cache::get( $filename . ':thumb' );
		else
		{
			$thumb = generateThumb( $path );
			Cache::put( $filename . ':thumb', $thumb, $thumb_exp );
		}
	}
	else if (($img = $app['DataLibrary']->get($filename)) && $img !== false) // load from new library
	{
		$mime = $app['DataLibrary']->getMimeType($filename);

		if( Cache::has( $filename . ':thumb' ) )
			$thumb = Cache::get( $filename . ':thumb' );
		else
		{
			$thumb_dir = storage_path('thumbs_wip' . '/' . str_random(8));
			$thumb_wip = $thumb_dir . '/' . $filename;
			mkdir($thumb_dir, 0775, true);
			file_put_contents($thumb_wip, $img);
			$thumb = generateThumb($thumb_wip);
			unlink($thumb_wip);
			rmdir($thumb_dir);
			Cache::put($filename . ':thumb', $thumb, $thumb_exp);
		}
	}
	
	if ($thumb != null)
		return response($thumb)->header('Content-Type', $mime)->header('X-App-Time', ( microtime(true) - $sTime ));
	else
	{
		Cache::forget( $filename .':thumb' ); 
		return response('Not found.', 404);
	}

});

$app->get('{file}', function( Request $request, $filename ) use ($app)
{
	$result = $app["DataLibrary"]->get($filename);

	if( $result !== false)
	{
		$response = response($result);
		
		$mime = $app["DataLibrary"]->getMimeType($filename);

		if($mime === false)
			$mime = "application/octet-string";

		return response($result)
			->header("Content-Type", $mime)
			->header("X-Delivered-By", "Content Library");
	}
		
	$file = split_filename( $filename );
	$path = env("GUEST_IMAGES_DIR") . '/' . $file;

	$sTime = microtime(true);

	if( file_exists( $path ) && is_file( $path ) )
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $path);
		finfo_close($finfo);

		return response(file_get_contents($path))->header('Content-Type', $mime)->header( 'X-App-Time', (microtime(true)-$sTime));
	}
	else
	{
		$content = $app['DataLibrary']->get($filename);

		if( $content !== false)
		{
			$mime = $app['DataLibrary']->getMimeType($filename);
			return response($content)->hader('Content-Type', $mime);
		}
		// Redis::hset( $filename . ':info', 'deleted', 1 );
		return response('Not found.', 404);
	}
});

$app->get('{user}/{file}', function( $user, $file )
{
	$filename = $user . '/' . $file;
    $path = env("USER_IMAGES_DIR") . '/' . $filename;

	if( file_exists( $path ) && is_file( $path ) )
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $path);
		finfo_close($finfo);
		
		return response(file_get_contents($path))->header('Content-Type', $mime);
	}
	else
	{
		return response('Not found.', 404);
	}

});
