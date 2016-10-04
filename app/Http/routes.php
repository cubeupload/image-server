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

$app->get('/', function () use ($app) {
	return redirect('http://cubeupload.com');
});

$app->get('proxytest', function()
{
	return var_dump( $_SERVER );
});

$app->get('/touchtest', function()
{
	$file = split_filename('1oiEfj.jpg');
	$path = env('GUEST_IMAGES_DIR') . '/' . $file;

	touch($path);
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

		if( Redis::exists( $filename . ':thumb' ) )
		{
			$thumb = Redis::get( $filename . ':thumb' );
			Redis::expire( $filename . ':thumb', $thumb_exp );
		}
		else
		{
			$thumb = generateThumb( $path );
			Redis::setex( $filename . ':thumb', $thumb_exp, $thumb );
		}

		return response($thumb)->header('Content-Type', $mime);
	}
	else
	{
		Redis::del( $filename .':thumb' );
		return response('Not found.', 404);
	}	
});

$app->get('/t/{file}', function( $filename )
{
	$file = split_filename( $filename );
	$path = env("GUEST_IMAGES_DIR") . '/' . $file;
	$thumb_exp = env("CACHE_THUMB_EXPIRE");
	$thumb = null;

	$sTime = microtime(true);

	if( file_exists( $path ) )
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $path);
		finfo_close($finfo);

		if( Redis::exists( $filename . ':thumb' ) )
		{
			$thumb = Redis::get( $filename . ':thumb' );
			Redis::expire( $filename . ':thumb', $thumb_exp );
		}
		else
		{
			$thumb = generateThumb( $path );
			Redis::setex( $filename . ':thumb', $thumb_exp, $thumb );
		}

		return response($thumb)->header('Content-Type', $mime)->header('X-App-Time', ( microtime(true) - $sTime ));
	}
	else
	{
		Redis::del( $filename .':thumb' ); 
		return response('Not found.', 404);
	}

});

$app->get('{file}', function( $filename )
{
	$file = split_filename( $filename );
	$path = env("GUEST_IMAGES_DIR") . '/' . $file;

	$sTime = microtime(true);

	if( file_exists( $path ) && is_file( $path ) )
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $path);
		finfo_close($finfo);

		/*
		try
		{
			Redis::hmset( $filename .':info', 'mimetype', $mime, 'lastview', time() );

			$viewer = [
				'ip' => Request::server('REMOTE_ADDR'),
				'at' => time(),
				'from' => Request::server('HTTP_REFERER')
			];
		
			Redis::sadd( $filename . ':views', json_encode($viewer));
		}
		catch( \Predis\Connection\ConnectionException $ex )
		{
			Log::error( 'ANON: ' . $filename . ' - ' . $ex->getMessage(), $ex );
		}
		*/
		return response(file_get_contents($path))->header('Content-Type', $mime)->header( 'X-App-Time', (microtime(true)-$sTime));
	}
	else
	{
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

		/*
		try
		{
			Redis::hmset( $filename .':info', 'mimetype', $mime, 'lastview', time() );

			$viewer = [
				'ip' => Request::server('REMOTE_ADDR'),
				'at' => time(),
				'from' => Request::server('HTTP_REFERER')
			];

			Redis::sadd( $filename . ':views', json_encode($viewer));
		}
		catch( \Predis\Connection\ConnectionException $ex )
		{
			Log::error( 'USER: ' . $path . ' - ' . $ex->getMessage() );
		}
		*/
		
		return response(file_get_contents($path))->header('Content-Type', $mime);
	}
	else
	{
		return response('Not found.', 404);
	}

});
