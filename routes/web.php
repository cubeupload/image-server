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

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->group(['middleware' => 'imgexists'], function () use ($app){
    $app->get('/{filename}', 'ImageController@getGuestImage');
    $app->get('/t/{filename}', 'ImageController@getGuestThumb');
    $app->get('/{username}/{filename}', 'ImageController@getUserImage');
    $app->get('/{username}/t/{filename}', 'ImageController@getUserThumb');
});

$app->group(['middleware' => 'purgeauth'], function () use ($app){
    $app->delete('/{filename}', 'ImageController@deleteGuestImage');
    $app->delete('/{username}/{filename}', 'ImageController@deleteUserImage');
});


$app->get('/hash/{hash}', 'ImageController@getHash');